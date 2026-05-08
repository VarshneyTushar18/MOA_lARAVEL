<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\ResearchPatient;

class ResearchPatientController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'ltbirs_no' => 'required',
            'file' => 'required|mimes:pdf,jpg,jpeg|max:2048',
        ]);

        $ltbirs = strtoupper($request->ltbirs_no);

        // Validate format
        if (!preg_match('/^LTBIRS\d{4}$/', $ltbirs)) {
            return back()->withErrors('Invalid LTBIRS format. Example: LTBIRS0001');
        }

        // Store File
        $file = $request->file('file');
        $filename = $ltbirs . '.' . $file->getClientOriginalExtension();
        $path = $file->storeAs('research', $filename);

        ResearchPatient::create([
            'ltbirs_no' => $ltbirs,
            'file_path' => $path
        ]);

        return back()->with('research_success', 'File uploaded successfully.');
    }

    public function download(Request $request)
    {
        $request->validate([
            'ltbirs_no' => 'required'
        ]);

        $record = ResearchPatient::where('ltbirs_no', strtoupper($request->ltbirs_no))->first();

        if (!$record) {
            return back()->withErrors('Record not found.');
        }

        return Storage::download($record->file_path);
    }
}