<?php

namespace App\Http\Controllers;

use App\Models\ResearchPatient;
use App\Services\CompressedUploadStorage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ResearchPatientController extends Controller
{
    public function index()
    {
        $records = ResearchPatient::orderByDesc('id')->paginate(50);

        return view('research_console.list', compact('records'));
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
}
