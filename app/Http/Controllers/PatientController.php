<?php

namespace App\Http\Controllers;

use App\Exports\PatientsExport;
use App\Exports\PatientsListExport;
use App\Http\Controllers\Concerns\FiltersConsoleDateRange;
use App\Http\Controllers\Concerns\HandlesBulkSelection;
use App\Imports\PatientsImport;
use App\Models\Patient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;

class PatientController extends Controller
{
    use FiltersConsoleDateRange, HandlesBulkSelection;
    // Save patient data
    public function store(Request $request)
    {
        $request->merge([
            'adhaar_no' => trim((string) $request->input('adhaar_no', '')),
            'age' => $request->filled('age') ? $request->input('age') : null,
        ]);

        $request->validate([
            'name' => ['required', 'string', 'min:2', 'max:255', 'regex:/^[\p{L}\p{M}\s.\'\-]+$/u'],
            'adhaar_no' => ['required', 'string', 'max:255'],
            'uhid_no' => 'required|string|max:255',
            'file_no' => 'nullable|string|max:100',
            'age' => 'nullable|integer|min:0|max:120',
        ], [
            'name.regex' => 'Name may only contain letters (including Hindi and other scripts), spaces, apostrophes, hyphens, and periods.',
        ]);

        Patient::create($request->all());

        return back()->with('success', 'Patient data has been submitted successfully!');
    }

    // Show list in admin
    public function index(Request $request)
    {
        $query = Patient::query()->orderByDesc('id');
        $filters = $this->dateRangeQuery($request, $query, 'date');
        $patients = $query->paginate(25)->withQueryString();

        return view('patient_console.list', compact('patients', 'filters'));
    }

    public function show($id)
    {
        $patient = Patient::findOrFail($id);

        return view('patient_console.show', compact('patient'));
    }

    public function edit($id)
    {
        $patient = Patient::findOrFail($id);

        return view('patient_console.edit', compact('patient'));
    }

    public function update(Request $request, $id)
    {
        $patient = Patient::findOrFail($id);

        $request->merge([
            'adhaar_no' => trim((string) $request->input('adhaar_no', '')),
            'age' => $request->filled('age') ? $request->input('age') : null,
        ]);

        $request->validate([
            'name' => ['required', 'string', 'min:2', 'max:255', 'regex:/^[\p{L}\p{M}\s.\'\-]+$/u'],
            'adhaar_no' => ['required', 'string', 'max:255'],
            'uhid_no' => 'required|string|max:255',
            'file_no' => 'nullable|string|max:100',
            'age' => 'nullable|integer|min:0|max:120',
        ], [
            'name.regex' => 'Name may only contain letters (including Hindi and other scripts), spaces, apostrophes, hyphens, and periods.',
        ]);

        $patient->update($request->only($patient->getFillable()));

        return redirect()
            ->route('console.patients.show', $patient->id)
            ->with('success', 'Patient record updated successfully.');
    }

    public function destroy($id)
    {
        Patient::findOrFail($id)->delete();

        return redirect()
            ->route('console.patients.index')
            ->with('success', 'Patient record deleted successfully.');
    }

    public function exportSelected(Request $request)
    {
        $ids = $this->validatedBulkIds($request);
        $patients = Patient::whereIn('id', $ids)->orderByDesc('id')->get();

        return Excel::download(
            new PatientsListExport($patients),
            'patients_selected_'.now()->format('Ymd_His').'.xlsx'
        );
    }

    public function exportAll(Request $request)
    {
        $query = Patient::query()->orderByDesc('id');
        $filters = $this->dateRangeQuery($request, $query, 'date');
        $patients = $query->get();
        $suffix = $this->hasDateRange($filters) ? 'filtered' : 'all';

        return Excel::download(
            new PatientsListExport($patients),
            'patients_'.$suffix.'_'.now()->format('Ymd_His').'.xlsx'
        );
    }

    public function bulkDestroy(Request $request)
    {
        if ($this->bulkUsesDateRange($request)) {
            $query = Patient::query();
            $this->dateRangeQuery($request, $query, 'date');
            $deleted = $query->delete();

            return redirect()
                ->route('console.patients.index', $request->only(['from_date', 'to_date']))
                ->with('success', $deleted.' patient record(s) deleted for the selected date range.');
        }

        $ids = $this->validatedBulkIds($request);
        $deleted = Patient::whereIn('id', $ids)->delete();

        return redirect()
            ->route('console.patients.index', $request->only(['from_date', 'to_date']))
            ->with('success', $deleted.' patient record(s) deleted successfully.');
    }

    public function search(Request $request)
    {
        if (! $request->has('q')) {
            $patients = Patient::query()->whereRaw('0 = 1')->paginate(20)->withQueryString();

            return view('pages.patient_search', compact('patients'));
        }

        $trimmed = trim((string) $request->input('q'));

        if ($trimmed === '') {
            return redirect()->route('patient.search')->withErrors([
                'q' => 'Please enter a search term.',
            ]);
        }

        $request->merge(['q' => $trimmed]);

        $validator = Validator::make(
            ['q' => $trimmed],
            ['q' => ['required', 'string', 'min:2', 'max:255']],
            ['q.min' => 'Please enter at least 2 characters to search.']
        );

        if ($validator->fails()) {
            return redirect()->route('patient.search')->withErrors($validator)->withInput();
        }

        $query = $validator->validated()['q'];

        $patients = Patient::where(function ($q) use ($query) {
            $q->where('name', 'LIKE', "%{$query}%")
            ->orWhere('uhid_no', 'LIKE', "%{$query}%")
            ->orWhere('adhaar_no', 'LIKE', "%{$query}%")
            ->orWhere('contact_details', 'LIKE', "%{$query}%");
        })
        ->paginate(20)
        ->withQueryString();

        return view('pages.patient_search', compact('patients'));
    }

    public function download($id)
{
    $patient = Patient::findOrFail($id);

    return Excel::download(new PatientsExport($patient->uhid_no), $patient->uhid_no . '_opd.xlsx');
}
    public function uploadOpd(Request $request)
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx,xls,csv'],
        ], [
            'file.mimes' => 'Upload a spreadsheet (.xlsx, .xls, or .csv).',
        ]);

        Excel::import(new PatientsImport, $request->file('file'));

        return back()->with('success', 'Excel Uploaded Successfully');
    }

    public function downloadOpd(Request $request)
    {
        $request->merge([
            'uhid_no' => strtoupper(trim((string) $request->input('uhid_no'))),
        ]);

        $request->validate([
            'uhid_no' => ['required', 'string', 'max:64', 'regex:/^LTBI\d{1,12}$/'],
        ], [
            'uhid_no.regex' => 'UHID must look like LTBI00035 (LTBI followed by digits).',
        ]);

        return Excel::download(
            new PatientsExport($request->uhid_no),
            $request->uhid_no . '_opd.xlsx'
        );
    }

public function downloadOpdByLast4AndFileNo(Request $request)
{
    $validated = $request->validate([
        'uhid_last4' => ['required', 'digits:4'],
        'file_no' => ['required', 'string', 'max:100'],
    ]);

    $patient = Patient::where('file_no', $validated['file_no'])
        ->whereRaw('RIGHT(uhid_no, 4) = ?', [$validated['uhid_last4']])
        ->first();

    if (!$patient) {
        return back()->withErrors([
            'uhid_last4' => 'No patient record found for provided last 4 UHID and File No.',
        ])->withInput();
    }

    return Excel::download(
        new PatientsExport($patient->uhid_no),
        $patient->uhid_no . '_opd.xlsx'
    );
}
}