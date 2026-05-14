<?php

namespace App\Http\Controllers\Console;

use App\Exports\SurveyResponsesExport;
use App\Http\Controllers\Controller;
use App\Imports\SurveyResponsesImport;
use App\Models\SurveyResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\File;
use Maatwebsite\Excel\Facades\Excel;
use Throwable;

class SurveyResponseController extends Controller
{
    public function index(Request $request)
    {
        $filters = $this->validatedFilters($request);
        $query = SurveyResponse::query()->latest();
        $this->applyFilters($query, $filters);
        $responses = $query->paginate(20)->appends($request->query());

        return view('console.survey_responses.index', compact('responses'));
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => [
                'required',
                File::types(['csv', 'xlsx', 'xls'])
                    ->max(15360),
            ],
        ], [
            'file.max' => 'The import file may not be larger than 15 MB.',
            'file.mimes' => 'Only CSV or Excel files are allowed (.csv, .xlsx, .xls).',
            'file.mimetypes' => 'Only CSV or Excel files are allowed (.csv, .xlsx, .xls).',
        ]);

        try {
            Excel::import(new SurveyResponsesImport, $request->file('file'));
        } catch (Throwable $e) {
            report($e);

            return back()->withInput()->withErrors([
                'file' => 'Import failed. Please verify the file format and column headers.',
            ]);
        }

        return back()->with('success', 'Survey data imported successfully.');
    }

    public function show(SurveyResponse $surveyResponse)
    {
        return view('console.survey_responses.show', [
            'response' => $surveyResponse,
        ]);
    }

    public function export(Request $request)
    {
        $filters = $this->validatedFilters($request);
        $query = SurveyResponse::query()->latest();
        $this->applyFilters($query, $filters);

        $filename = 'survey_responses_'.now()->format('Ymd_His').'.xlsx';

        return Excel::download(new SurveyResponsesExport($query->get()), $filename);
    }

    public function exportSelected(Request $request)
    {
        $validated = $request->validate([
            'selected_ids' => ['required', 'array', 'min:1'],
            'selected_ids.*' => ['integer', 'exists:survey_responses,id'],
        ]);

        $rows = SurveyResponse::whereIn('id', $validated['selected_ids'])->latest()->get();
        $filename = 'survey_responses_selected_'.now()->format('Ymd_His').'.xlsx';

        return Excel::download(new SurveyResponsesExport($rows), $filename);
    }

    public function sampleFile()
    {
        $path = storage_path('app/samples/survey_import_sample_new.csv');

        if (! file_exists($path)) {
            return back()->withErrors([
                'file' => 'Sample file not found.',
            ]);
        }

        return response()->download($path, 'survey_import_sample_new.csv', [
            'Content-Type' => 'text/csv',
        ]);
    }

    public function destroy(SurveyResponse $surveyResponse)
    {
        $surveyResponse->delete();

        return back()->with('success', 'Survey response deleted successfully.');
    }

    public function bulkDestroy(Request $request)
    {
        $validated = $request->validate([
            'selected_ids' => ['required', 'array', 'min:1'],
            'selected_ids.*' => ['integer', 'exists:survey_responses,id'],
        ]);

        $deleted = SurveyResponse::whereIn('id', $validated['selected_ids'])->delete();

        return back()->with('success', $deleted.' survey response(s) deleted successfully.');
    }

    private function validatedFilters(Request $request): array
    {
        return $request->validate([
            'q' => ['nullable', 'string', 'max:255'],
            'gender' => ['nullable', Rule::in(['Male', 'Female'])],
            'type_of_case' => ['nullable', Rule::in(['MDR', 'XDR', 'PRIMARY_CASE'])],
            'from_date' => ['nullable', 'date'],
            'to_date' => ['nullable', 'date', 'after_or_equal:from_date'],
        ]);
    }

    private function applyFilters($query, array $filters): void
    {
        if (! empty($filters['q'])) {
            $search = trim((string) $filters['q']);
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('contact_details', 'like', "%{$search}%")
                    ->orWhere('registration_number', 'like', "%{$search}%");
            });
        }

        if (! empty($filters['gender'])) {
            $query->where('gender', $filters['gender']);
        }

        if (! empty($filters['type_of_case'])) {
            $query->where('answers->type_of_case', $filters['type_of_case']);
        }

        if (! empty($filters['from_date'])) {
            $query->whereDate('survey_date', '>=', $filters['from_date']);
        }

        if (! empty($filters['to_date'])) {
            $query->whereDate('survey_date', '<=', $filters['to_date']);
        }
    }
}
