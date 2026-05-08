<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IdCard extends Model
{
    protected $fillable = [
        'id_number',
        'file_path'
    ];
}