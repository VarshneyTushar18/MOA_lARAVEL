<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\IdCard;

class IdCardController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'id_number' => 'required',
            'file' => 'required|mimes:pdf,jpg,jpeg|max:2048',
        ]);

        $idNumber = strtoupper($request->id_number);

        // Validate format (ID0001)
        if (!preg_match('/^ID\d{4}$/', $idNumber)) {
            return back()->withErrors('Invalid ID format. Example: ID0001');
        }

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
        $request->validate([
            'id_number' => 'required'
        ]);

        $record = IdCard::where('id_number', strtoupper($request->id_number))->first();

        if (!$record) {
            return back()->withErrors('ID Card not found.');
        }

        return Storage::download($record->file_path);
    }
}