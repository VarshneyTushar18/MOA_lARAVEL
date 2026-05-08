<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SurveyResponse extends Model
{
    use HasFactory;

    protected $fillable = [
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
        'answers',
    ];

    protected $casts = [
        'answers' => 'array',
        'survey_date' => 'date',
    ];
}
