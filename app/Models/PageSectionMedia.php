<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PageSectionMedia extends Model
{
    protected $fillable = [
        'page_section_id',
        'type',
        'file_path',
        'youtube_url'
    ];

    public function section()
    {
        return $this->belongsTo(PageSection::class);
    }
}
