<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\CurePatient;

class CureController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'ltbi_no' => 'required',
            'file' => 'required|mimes:pdf,jpg,jpeg|max:2048',
        ]);

        if (!$request->cc_no && !$request->tr_no) {
            return back()->withErrors('Either CC or TR number is required.');
        }

        // Generate Access Code
        $ltbi_last4 = substr(str_pad($request->ltbi_no, 5, '0', STR_PAD_LEFT), -4);

        if ($request->cc_no) {
            $last3 = substr(str_pad($request->cc_no, 5, '0', STR_PAD_LEFT), -3);
        } else {
            $last3 = substr(str_pad($request->tr_no, 5, '0', STR_PAD_LEFT), -3);
        }

        $access_code = $ltbi_last4 . $last3;

        // Store File
        $file = $request->file('file');
        $filename = $access_code . '.' . $file->getClientOriginalExtension();
        $path = $file->storeAs('cure', $filename);

        // Save DB
        CurePatient::create([
            'ltbi_no' => $request->ltbi_no,
            'cc_no' => $request->cc_no,
            'tr_no' => $request->tr_no,
            'access_code' => $access_code,
            'file_path' => $path,
        ]);

        return back()->with('cure_success', 'File Uploaded Successfully. Access Code: ' . $access_code);
    }


    public function download(Request $request)
    {
        $request->validate([
            'access_code' => 'required'
        ]);

        $record = CurePatient::where('access_code', $request->access_code)->first();

        if (!$record) {
            return back()->withErrors('Invalid Access Code.');
        }

        return Storage::download($record->file_path);
    }
}