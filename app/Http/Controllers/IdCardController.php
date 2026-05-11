<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\IdCard;

class IdCardController extends Controller
{
    public function store(Request $request)
    {
        $request->merge([
            'id_number' => strtoupper(trim((string) $request->input('id_number'))),
        ]);

        $validated = $request->validate([
            'id_number' => ['required', 'string', 'regex:/^ID\d{4}$/'],
            'file' => ['required', 'file', 'mimes:pdf,jpg,jpeg', 'max:2048'],
        ], [
            'id_number.regex' => 'ID must look like ID0001 (ID + 4 digits).',
        ]);

        $idNumber = $validated['id_number'];

        // Store file
        $file = $request->file('file');
        $filename = $idNumber . '.' . $file->getClientOriginalExtension();
        $path = $file->storeAs('id_cards', $filename);

        IdCard::create([
            'id_number' => $idNumber,
            'file_path' => $path
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

        if (!$record) {
            return back()->withErrors([
                'id_number' => 'ID card file not found for this number.',
            ]);
        }

        return Storage::download($record->file_path);
    }
}