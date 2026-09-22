<?php

namespace App\Http\Controllers;

use App\Models\IdCard;
use App\Services\CompressedUploadStorage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class IdCardController extends Controller
{
    public function index()
    {
        $records = IdCard::orderByDesc('id')->paginate(50);

        return view('idcard_console.list', compact('records'));
    }

    public function show($id)
    {
        $record = IdCard::findOrFail($id);

        return view('idcard_console.show', compact('record'));
    }

    public function consoleFile($id)
    {
        $record = IdCard::findOrFail($id);

        if (! $record->file_path || ! Storage::disk('local')->exists($record->file_path)) {
            abort(404);
        }

        return Storage::disk('local')->response($record->file_path);
    }

    public function consoleDownload($id)
    {
        $record = IdCard::findOrFail($id);

        if (! $record->file_path || ! Storage::disk('local')->exists($record->file_path)) {
            return back()->with('message', 'File not found on disk.');
        }

        return Storage::disk('local')->download($record->file_path);
    }

    public function store(Request $request)
    {
        $request->merge([
            'id_number' => strtoupper(trim((string) $request->input('id_number'))),
        ]);

        $validated = $request->validate([
            'id_number' => ['required', 'string', 'regex:/^ID\d{4}$/'],
            'file' => ['required', 'file', 'mimes:pdf,jpg,jpeg'],
        ], [
            'id_number.regex' => 'ID must look like ID0001 (ID + 4 digits).',
        ]);

        $idNumber = $validated['id_number'];

        if (IdCard::where('id_number', $idNumber)->exists()) {
            return back()
                ->withInput()
                ->withErrors([
                    'id_number' => 'This ID number is already uploaded. You cannot upload it again.',
                ]);
        }

        // Store file
        $file = $request->file('file');
        $filename = $idNumber.'.'.$file->getClientOriginalExtension();
        $path = CompressedUploadStorage::storeImageAs($file, 'id_cards', $filename);

        IdCard::create([
            'id_number' => $idNumber,
            'file_path' => $path,
        ]);

        return back()->with('id_success', 'ID Card uploaded successfully.');
    }

    public function download(Request $request)
    {
        $request->merge([
            'id_number' => strtoupper(trim((string) $request->input('id_number'))),
        ]);

        $request->validate([
            'id_number' => ['required', 'string', 'regex:/^ID\d{4}$/'],
        ], [
            'id_number.regex' => 'ID must look like ID0001 (ID + 4 digits).',
        ]);

        $record = IdCard::where('id_number', $request->id_number)->first();

        if (! $record) {
            return back()->withErrors([
                'id_number' => 'ID card file not found for this number.',
            ]);
        }

        return Storage::download($record->file_path);
    }
}
