<?php

namespace App\Http\Controllers;

use App\Models\SurveyResponse;
use Illuminate\Http\Request;

class SurveyResponseController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
    'name' => 'nullable|string|max:255',

    'address' => 'required|string|max:1000',
    'contact_details' => 'required|string|max:20',
    'gender' => 'nullable|in:Male,Female,Male (पुरुष),Female (महिला)',
    'age' => 'nullable|integer|min:0|max:120',

    'registration_number' => 'required|string|max:255',
    'survey_date' => 'nullable|date',

    'email' => 'nullable|email|max:255',
    'phone' => 'nullable|string|max:20',

    'message' => 'nullable|string|max:2000',
]);

        $answers = $request->except([
            '_token',
            'name',
            'address',
            'contact_details',
            'gender',
            'age',
            'registration_number',
            'survey_date',
            'email',
            'phone',
            'message',
        ]);

        SurveyResponse::create([
            'name' => $validated['name'] ?? null,
            'address' => $validated['address'] ?? null,
            'contact_details' => $validated['contact_details'] ?? null,
            'gender' => $validated['gender'] ?? null,
            'age' => $validated['age'] ?? null,
            'registration_number' => $validated['registration_number'] ?? null,
            'survey_date' => $validated['survey_date'] ?? null,
            'email' => $validated['email'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'message' => $validated['message'] ?? null,
            'answers' => $answers,
        ]);

        return back()->with('success', 'Survey submitted successfully.');
    }
}
