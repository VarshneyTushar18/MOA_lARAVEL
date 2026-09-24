<?php
require __DIR__.'/vendor/autoload.php';
$app = require __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$page = App\Models\Page::where('slug', 'acsm_iec')->orWhere('slug', 'acsm-iec')->first();
if (!$page) {
    $page = App\Models\Page::where('title', 'like', '%ACSM%')->first();
}
if (!$page) {
    echo "page not found\n";
    exit(1);
}
echo "page id={$page->id} slug={$page->slug} title={$page->title}\n";

$sections = App\Models\PageSection::where('page_id', $page->id)
    ->where(function ($q) {
        $q->where('section_key', 'documentaries_and_video_clips')
            ->orWhere('title', 'like', '%documentar%');
    })
    ->get(['id', 'section_key', 'title', 'parent_id']);

foreach ($sections as $s) {
    $videos = $s->media()->where('type', 'video')->count();
    echo "{$s->id} | key={$s->section_key} | title={$s->title} | parent={$s->parent_id} | videos=$videos\n";
}
