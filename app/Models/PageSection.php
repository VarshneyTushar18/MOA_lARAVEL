<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PageSection extends Model
{
    use HasFactory;

    protected $fillable = [
        'page_id', 'section_key', 'type', 'parent_id', 'title',
        'description', 'text_color', 'bg_color', 'image', 'pdf', 'videos', 'sort_order'
    ];

    public function page() {
        return $this->belongsTo(Page::class);
    }

    public function subsections() {
        return $this->hasMany(PageSection::class, 'parent_id')->orderBy('sort_order');
    }

    public function parent() {
        return $this->belongsTo(PageSection::class, 'parent_id');
    }

    public function images() {
        return $this->hasMany(PageSectionImage::class);
    }

    // Videos accessor (returns array)
    public function getVideosAttribute($value)
{
    return $value ? json_decode($value, true) : [];
}
public function media()
{
    return $this->hasMany(PageSectionMedia::class);
}

public function highlightItems()
{
    return $this->hasMany(PageSectionHighlightItem::class, 'page_section_id')->orderBy('sort_order');
}
}