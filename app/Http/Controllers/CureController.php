<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\CurePatient;

class CureController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'type' => ['required', 'in:cc,tr'],
            'ltbi_no' => ['required', 'string', 'regex:/^[0-9]{1,12}$/'],
            'cc_no' => ['nullable', 'required_if:type,cc', 'string', 'regex:/^[0-9]{1,12}$/'],
            'tr_no' => ['nullable', 'required_if:type,tr', 'string', 'regex:/^[0-9]{1,12}$/'],
            'file' => ['required', 'file', 'mimes:pdf,jpg,jpeg', 'max:2048'],
        ], [
            'ltbi_no.regex' => 'LTBI number must be numeric only.',
            'cc_no.regex' => 'CC number must be numeric only.',
            'tr_no.regex' => 'TR number must be numeric only.',
            'cc_no.required_if' => 'Enter CC number when CC Number type is selected.',
            'tr_no.required_if' => 'Enter TR number when TR Number type is selected.',
        ]);

        // Generate Access Code
        $ltbi_last4 = substr(str_pad($validated['ltbi_no'], 5, '0', STR_PAD_LEFT), -4);

        if ($validated['type'] === 'cc') {
            $last3 = substr(str_pad($validated['cc_no'], 5, '0', STR_PAD_LEFT), -3);
        } else {
            $last3 = substr(str_pad($validated['tr_no'], 5, '0', STR_PAD_LEFT), -3);
        }

        $access_code = $ltbi_last4 . $last3;

        // Store File
        $file = $request->file('file');
        $filename = $access_code . '.' . $file->getClientOriginalExtension();
        $path = $file->storeAs('cure', $filename);

        // Save DB
        CurePatient::create([
            'ltbi_no' => $validated['ltbi_no'],
            'cc_no' => $validated['cc_no'],
            'tr_no' => $validated['tr_no'],
            'access_code' => $access_code,
            'file_path' => $path,
        ]);

        return back()->with('cure_success', 'File Uploaded Successfully. Access Code: ' . $access_code);
    }


    public function download(Request $request)
    {
        $request->validate([
            'access_code' => ['required', 'string', 'regex:/^[0-9]{7}$/'],
        ], [
            'access_code.regex' => 'Access code must be exactly 7 digits.',
        ]);

        $record = CurePatient::where('access_code', $request->input('access_code'))->first();

        if (!$record) {
            return back()->withErrors([
                'access_code' => 'Invalid access code.',
            ]);
        }

        return Storage::download($record->file_path);
    }
}