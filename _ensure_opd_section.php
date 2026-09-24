<?php

require __DIR__.'/vendor/autoload.php';
$app = require __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Page;
use App\Models\PageSection;

$page = Page::where('slug', 'best_practices')->firstOrFail();

$section = PageSection::query()
    ->where('page_id', $page->id)
    ->where('section_key', 'opd_videos_and_photographs')
    ->first();

if (! $section) {
    $section = PageSection::create([
        'page_id' => $page->id,
        'section_key' => 'opd_videos_and_photographs',
        'title' => 'OPD videos and photograps',
        'description' => null,
        'sort_order' => 10,
        'parent_id' => null,
    ]);
    echo "created section id={$section->id}\n";
} else {
    $section->title = 'OPD videos and photograps';
    $section->save();
    echo "existing section id={$section->id}\n";
}

echo 'page_id='.$page->id."\n";
echo 'videos='.$section->media()->where('type', 'video')->count()."\n";
echo 'images='.$section->images()->count()."\n";
