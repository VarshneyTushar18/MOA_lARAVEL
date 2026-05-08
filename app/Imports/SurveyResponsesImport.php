<?php

namespace App\Imports;

use App\Models\SurveyResponse;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class SurveyResponsesImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        return new SurveyResponse([
            'name' => $row['name'] ?? null,
            'address' => $row['address'] ?? null,
            'contact_details' => $row['contact_details'] ?? null,
            'gender' => $row['gender'] ?? null,
            'age' => $row['age'] ?? null,
            'registration_number' => $row['registration_number'] ?? null,
            'survey_date' => $row['survey_date'] ?? null,
            'email' => $row['email'] ?? null,
            'phone' => $row['phone'] ?? null,
            'message' => $row['message'] ?? null,

            'answers' => $row,
        ]);
    }
}
