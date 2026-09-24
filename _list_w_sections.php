<?php
require __DIR__.'/vendor/autoload.php';
$app = require __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$parent = App\Models\PageSection::where('section_key', 'training_survey')->first();
if (!$parent) { echo "no parent\n"; exit(1); }

foreach ($parent->subsections()->orderBy('sort_order')->orderBy('id')->get(['id','section_key','title']) as $s) {
    $videos = $s->media()->where('type', 'video')->count();
    $images = $s->images()->count();
    echo "{$s->id} | {$s->section_key} | {$s->title} | videos=$videos images=$images\n";
}
