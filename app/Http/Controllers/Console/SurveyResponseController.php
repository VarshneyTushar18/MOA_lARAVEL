<?php

namespace App\Http\Controllers\Console;

use App\Http\Controllers\Controller;
use App\Imports\SurveyResponsesImport;
use App\Models\SurveyResponse;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class SurveyResponseController extends Controller
{
    public function index()
    {
        $responses = SurveyResponse::latest()->paginate(20);

        return view('console.survey_responses.index', compact('responses'));
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:csv,xlsx,xls',
        ]);

        Excel::import(new SurveyResponsesImport, $request->file('file'));

        return back()->with('success', 'Survey data imported successfully.');
    }
}
