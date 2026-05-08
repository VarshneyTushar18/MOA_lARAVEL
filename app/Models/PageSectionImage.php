<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PageSectionImage extends Model
{
    use HasFactory;

    protected $fillable = [
        'page_section_id',
        'image'
    ];

    public function section() {
        return $this->belongsTo(PageSection::class, 'page_section_id');
    }
}