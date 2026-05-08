<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CurePatient extends Model
{
    protected $fillable = [
        'ltbi_no',
        'cc_no',
        'tr_no',
        'access_code',
        'file_path'
    ];
}