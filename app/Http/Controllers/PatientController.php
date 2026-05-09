<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Patient;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\PatientsExport;
use App\Imports\PatientsImport;

class PatientController extends Controller
{
    // Save patient data
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'adhaar_no' => ['required', 'digits:16'], // ✅ exactly 16 digits
            'uhid_no' => 'required|string|max:255',
            'file_no' => 'nullable|string|max:100',
            // Add any other validations you want
        ], [
            'adhaar_no.digits' => 'Aadhaar number must be exactly 16 digits.', // custom error message
        ]);

        Patient::create($request->all());

        return back()->with('success', 'Patient data has been submitted successfully!');
    }

    // Show list in admin
    public function index()
    {
        $patients = Patient::orderBy('id', 'asc')->paginate(10); // or ->get() if not paginating
        return view('patient_console.list', compact('patients'));
    }

    public function search(Request $request)
    {
        $query = $request->q;

        $patients = Patient::where(function($q) use ($query) {
            $q->where('name', 'LIKE', "%$query%")
            ->orWhere('uhid_no', 'LIKE', "%$query%")
            ->orWhere('adhaar_no', 'LIKE', "%$query%")
            ->orWhere('contact_details', 'LIKE', "%$query%");
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

        Excel::import(new PatientsImport, $request->file('file'));

        return back()->with('success','Excel Uploaded Successfully');
    }
    public function downloadOpd(Request $request)
{
    $request->validate([
        'uhid_no' => 'required'
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