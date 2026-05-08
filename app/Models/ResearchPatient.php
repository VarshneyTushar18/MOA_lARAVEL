<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ResearchPatient extends Model
{
    protected $fillable = [
        'ltbirs_no',
        'file_path'
    ];
}