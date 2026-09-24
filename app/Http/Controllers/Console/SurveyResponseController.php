<?php

namespace App\Http\Controllers\Console;

use App\Exports\SurveyResponsesExport;
use App\Http\Controllers\Concerns\HandlesBulkSelection;
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
    use HandlesBulkSelection;

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
                File::types(['csv', 'xlsx', 'xls']),
            ],
        ], [
            'file.mimes' => 'Wrong file type. Use .csv, .xlsx, or .xls only.',
            'file.mimetypes' => 'Wrong file type. Use .csv, .xlsx, or .xls only.',
        ]);

        try {
            Excel::import(new SurveyResponsesImport, $request->file('file'));
        } catch (Throwable $e) {
            report($e);

            return back()->withInput()->withErrors([
                'file' => 'Wrong file or layout. Use CSV/Excel with the sample columns.',
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

    public function edit(SurveyResponse $surveyResponse)
    {
        return view('console.survey_responses.edit', [
            'response' => $surveyResponse,
            'answerFields' => $this->answerFieldLabels(),
        ]);
    }

    public function update(Request $request, SurveyResponse $surveyResponse)
    {
        $request->merge([
            'age' => $request->filled('age') ? $request->input('age') : null,
        ]);

        $validated = $request->validate([
            'name' => ['required', 'string', 'min:2', 'max:255'],
            'address' => 'required|string|max:1000',
            'contact_details' => 'required|string|max:30',
            'gender' => 'nullable|in:Male,Female',
            'age' => 'nullable|integer|min:0|max:120',
            'registration_number' => 'required|string|max:255',
            'survey_date' => 'nullable|date',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'message' => 'nullable|string|max:2000',
            'answers' => 'nullable|array',
            'answers.*' => 'nullable|string|max:4000',
        ]);

        $answers = [];
        $arrayFields = ['persistent_digestive_defecation_complaints', 'known_immunosuppression'];

        foreach ($this->answerFieldLabels() as $key => $label) {
            $value = trim((string) data_get($validated, "answers.$key", ''));
            if ($value === '') {
                continue;
            }

            if (in_array($key, $arrayFields, true)) {
                $answers[$key] = array_values(array_filter(array_map('trim', explode(',', $value))));
                continue;
            }

            $answers[$key] = $value;
        }

        $surveyResponse->update([
            'name' => $validated['name'],
            'address' => $validated['address'],
            'contact_details' => $validated['contact_details'],
            'gender' => $validated['gender'] ?? null,
            'age' => $validated['age'] ?? null,
            'registration_number' => $validated['registration_number'],
            'survey_date' => $validated['survey_date'] ?? null,
            'email' => $validated['email'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'message' => $validated['message'] ?? null,
            'answers' => $answers,
        ]);

        return redirect()
            ->route('console.survey_responses.show', $surveyResponse)
            ->with('success', 'Survey response updated successfully.');
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

        return redirect()
            ->route('console.survey_responses.index')
            ->with('success', 'Survey response deleted successfully.');
    }

    public function bulkDestroy(Request $request)
    {
        if ($this->bulkUsesDateRange($request)) {
            $filters = $this->validatedFilters($request);
            $query = SurveyResponse::query();
            $this->applyFilters($query, $filters);
            $deleted = $query->delete();

            return redirect()
                ->route('console.survey_responses.index', $request->query())
                ->with('success', $deleted.' survey response(s) deleted for the selected date range.');
        }

        $ids = $this->validatedBulkIds($request);
        $deleted = SurveyResponse::whereIn('id', $ids)->delete();

        return redirect()
            ->route('console.survey_responses.index', $request->query())
            ->with('success', $deleted.' survey response(s) deleted successfully.');
    }

    private function answerFieldLabels(): array
    {
        return [
            'illness_or_medication_history' => 'History of illness or medication',
            'frequent_cold_or_respiratory_allergy' => 'Frequent cold / respiratory allergy',
            'persistent_digestive_defecation_complaints' => 'Digestive complaints (comma-separated)',
            'unable_to_gain_weight_or_weight_loss' => 'Unable to gain weight / weight loss',
            'excessive_anger_or_stress_irritability' => 'Excessive anger / stress',
            'persistent_bodyache_or_fatigue' => 'Persistent bodyache / fatigue',
            'lack_of_enthusiasm_or_energy' => 'Lack of enthusiasm / energy',
            'irregular_menses_amenorrhea_or_infertility' => 'Irregular menses / amenorrhea / infertility',
            'frequent_hospital_visits' => 'Frequent hospital visits',
            'difficulty_or_pain_in_joint_movements' => 'Difficulty / pain in joint movements',
            'frequent_headache_dizziness_lightheadedness' => 'Frequent headache / dizziness',
            'risk_stage' => 'Risk stage',
            'known_immunosuppression' => 'Known immunosuppression (comma-separated)',
            'fever' => 'Fever',
            'cough_with_sputum_more_than_3_weeks' => 'Cough with sputum > 3 weeks',
            'difficulty_in_breathing' => 'Difficulty in breathing',
            'blood_in_sputum' => 'Blood in sputum',
            'weight_loss_with_fever' => 'Weight loss with fever',
            'chest_pain' => 'Chest pain',
            'blood_in_urine' => 'Blood in urine',
            'recurrent_diarrhea_loss_of_appetite_abdominal_distension_pain' => 'Recurrent diarrhea / appetite loss / abdominal distension',
            'other_relevant_information_by_screening_officer' => 'Other relevant information by screening officer',
            'previous_treatment_of_tb_and_duration' => 'Previous TB treatment and duration',
            'history_of_extra_pulmonary_tb_details' => 'History of extra pulmonary TB',
            'family_history_of_tb' => 'Family history of TB',
            'contact_to_tb_patient' => 'Contact to TB patient',
            'contact_to_mdr_tb_patient' => 'Contact to MDR-TB patient',
            'history_of_incomplete_tb_treatment' => 'History of incomplete TB treatment',
            'type_of_case' => 'Type of case',
            'remarks' => 'Remarks / pledge',
            'patient_feedback_form_text' => 'Patient feedback form text',
            'aware_of_latent_tb_before_2023' => 'Aware of latent TB before 2023',
            'aware_of_latent_tb_now' => 'Now aware of latent TB',
            'source_of_information_on_latent_tb' => 'Source of latent TB information',
            'aware_of_ongoing_phi_project' => 'Aware of ongoing PHI project',
            'satisfied_with_information_provided' => 'Satisfied with information provided',
            'investigator_name_designation_affiliation_email' => 'Investigator name, designation, affiliation, email',
        ];
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
