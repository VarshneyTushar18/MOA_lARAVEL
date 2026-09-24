<?php

namespace App\Http\Controllers;

use App\Exports\ResearchPatientsExport;
use App\Http\Controllers\Concerns\FiltersConsoleDateRange;
use App\Http\Controllers\Concerns\HandlesBulkSelection;
use App\Models\ResearchPatient;
use App\Services\CompressedUploadStorage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class ResearchPatientController extends Controller
{
    use FiltersConsoleDateRange, HandlesBulkSelection;

    public function index(Request $request)
    {
        $query = ResearchPatient::query()->orderByDesc('id');
        $filters = $this->dateRangeQuery($request, $query, 'created_at');
        $records = $query->paginate(25)->withQueryString();

        return view('research_console.list', compact('records', 'filters'));
    }

    public function show($id)
    {
        $record = ResearchPatient::findOrFail($id);

        return view('research_console.show', compact('record'));
    }

    public function consoleFile($id)
    {
        $record = ResearchPatient::findOrFail($id);

        if (! $record->file_path || ! Storage::disk('local')->exists($record->file_path)) {
            abort(404);
        }

        return Storage::disk('local')->response($record->file_path);
    }

    public function consoleDownload($id)
    {
        $record = ResearchPatient::findOrFail($id);

        if (! $record->file_path || ! Storage::disk('local')->exists($record->file_path)) {
            return back()->with('message', 'File not found on disk.');
        }

        return Storage::disk('local')->download($record->file_path);
    }

    public function edit($id)
    {
        $record = ResearchPatient::findOrFail($id);

        return view('research_console.edit', compact('record'));
    }

    public function update(Request $request, $id)
    {
        $record = ResearchPatient::findOrFail($id);

        $request->merge([
            'ltbirs_no' => strtoupper(trim((string) $request->input('ltbirs_no'))),
        ]);

        $validated = $request->validate([
            'ltbirs_no' => ['required', 'string', 'regex:/^LTBIRS\d{4}$/'],
            'file' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg'],
        ], [
            'ltbirs_no.regex' => 'LTBIRS number must look like LTBIRS0001 (LTBIRS + 4 digits).',
        ]);

        if (ResearchPatient::where('ltbirs_no', $validated['ltbirs_no'])->where('id', '!=', $record->id)->exists()) {
            return back()->withInput()->withErrors([
                'ltbirs_no' => 'This LTBIRS number is already used by another record.',
            ]);
        }

        if ($request->hasFile('file')) {
            if ($record->file_path && Storage::disk('local')->exists($record->file_path)) {
                Storage::disk('local')->delete($record->file_path);
            }

            $file = $request->file('file');
            $filename = $validated['ltbirs_no'].'.'.$file->getClientOriginalExtension();
            $validated['file_path'] = CompressedUploadStorage::storeImageAs($file, 'research', $filename);
        }

        $record->update([
            'ltbirs_no' => $validated['ltbirs_no'],
            'file_path' => $validated['file_path'] ?? $record->file_path,
        ]);

        return redirect()
            ->route('console.research.show', $record->id)
            ->with('success', 'Research upload updated successfully.');
    }

    public function destroy($id)
    {
        $this->deleteRecord(ResearchPatient::findOrFail($id));

        return redirect()
            ->route('console.research.list')
            ->with('success', 'Research upload deleted successfully.');
    }

    public function exportSelected(Request $request)
    {
        $ids = $this->validatedBulkIds($request);
        $records = ResearchPatient::whereIn('id', $ids)->orderByDesc('id')->get();

        return Excel::download(
            new ResearchPatientsExport($records),
            'research_uploads_selected_'.now()->format('Ymd_His').'.xlsx'
        );
    }

    public function exportAll(Request $request)
    {
        $query = ResearchPatient::query()->orderByDesc('id');
        $filters = $this->dateRangeQuery($request, $query, 'created_at');
        $records = $query->get();
        $suffix = $this->hasDateRange($filters) ? 'filtered' : 'all';

        return Excel::download(
            new ResearchPatientsExport($records),
            'research_uploads_'.$suffix.'_'.now()->format('Ymd_His').'.xlsx'
        );
    }

    public function bulkDestroy(Request $request)
    {
        if ($this->bulkUsesDateRange($request)) {
            $query = ResearchPatient::query();
            $this->dateRangeQuery($request, $query, 'created_at');
            $records = $query->get();

            foreach ($records as $record) {
                $this->deleteRecord($record);
            }

            return redirect()
                ->route('console.research.list', $request->only(['from_date', 'to_date']))
                ->with('success', $records->count().' research upload(s) deleted for the selected date range.');
        }

        $ids = $this->validatedBulkIds($request);
        $records = ResearchPatient::whereIn('id', $ids)->get();

        foreach ($records as $record) {
            $this->deleteRecord($record);
        }

        return redirect()
            ->route('console.research.list', $request->only(['from_date', 'to_date']))
            ->with('success', $records->count().' research upload(s) deleted successfully.');
    }

    public function store(Request $request)
    {
        $request->merge([
            'ltbirs_no' => strtoupper(trim((string) $request->input('ltbirs_no'))),
        ]);

        $validated = $request->validate([
            'ltbirs_no' => ['required', 'string', 'regex:/^LTBIRS\d{4}$/'],
            'file' => ['required', 'file', 'mimes:pdf,jpg,jpeg'],
        ], [
            'ltbirs_no.regex' => 'LTBIRS number must look like LTBIRS0001 (LTBIRS + 4 digits).',
        ]);

        $ltbirs = $validated['ltbirs_no'];

        if (ResearchPatient::where('ltbirs_no', $ltbirs)->exists()) {
            return back()
                ->withInput()
                ->withErrors([
                    'ltbirs_no' => 'This LTBIRS number is already uploaded. You cannot upload it again.',
                ]);
        }

        // Store File
        $file = $request->file('file');
        $filename = $ltbirs.'.'.$file->getClientOriginalExtension();
        $path = CompressedUploadStorage::storeImageAs($file, 'research', $filename);

        ResearchPatient::create([
            'ltbirs_no' => $ltbirs,
            'file_path' => $path,
        ]);

        return back()->with('research_success', 'File uploaded successfully.');
    }

    public function download(Request $request)
    {
        $request->merge([
            'ltbirs_no' => strtoupper(trim((string) $request->input('ltbirs_no'))),
        ]);

        $request->validate([
            'ltbirs_no' => ['required', 'string', 'regex:/^LTBIRS\d{4}$/'],
        ], [
            'ltbirs_no.regex' => 'LTBIRS number must look like LTBIRS0001 (LTBIRS + 4 digits).',
        ]);

        $record = ResearchPatient::where('ltbirs_no', $request->ltbirs_no)->first();

        if (! $record) {
            return back()->withErrors([
                'ltbirs_no' => 'Record not found for this number.',
            ]);
        }

        return Storage::download($record->file_path);
    }

    private function deleteRecord(ResearchPatient $record): void
    {
        if ($record->file_path && Storage::disk('local')->exists($record->file_path)) {
            Storage::disk('local')->delete($record->file_path);
        }

        $record->delete();
    }
}
