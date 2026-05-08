<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PageSectionHighlightItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'page_section_id',
        'title',
        'description',
        'image',
        'youtube_url',
        'video_path',
        'sort_order',
    ];

    public function section()
    {
        return $this->belongsTo(PageSection::class, 'page_section_id');
    }
}
