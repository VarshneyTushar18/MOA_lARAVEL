<?php

namespace App\Http\Controllers;

use App\Models\ResearchPatient;
use App\Services\CompressedUploadStorage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ResearchPatientController extends Controller
{
    public function store(Request $request)
    {
        $request->merge([
            'ltbirs_no' => strtoupper(trim((string) $request->input('ltbirs_no'))),
        ]);

        $validated = $request->validate([
            'ltbirs_no' => ['required', 'string', 'regex:/^LTBIRS\d{4}$/'],
            'file' => ['required', 'file', 'mimes:pdf,jpg,jpeg', 'max:2048'],
        ], [
            'ltbirs_no.regex' => 'LTBIRS number must look like LTBIRS0001 (LTBIRS + 4 digits).',
        ]);

        $ltbirs = $validated['ltbirs_no'];

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
}
