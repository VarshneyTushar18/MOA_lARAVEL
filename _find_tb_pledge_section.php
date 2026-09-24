<?php
require __DIR__.'/vendor/autoload.php';
$app = require __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$page = App\Models\Page::where('slug', 'acsm_iec')->first();
foreach (App\Models\PageSection::where('page_id', $page->id)->where('section_key', 'tb_pledge')->get() as $s) {
    $videos = $s->media()->where('type', 'video')->count();
    echo "{$s->id} | key={$s->section_key} | title={$s->title} | videos=$videos\n";
}
