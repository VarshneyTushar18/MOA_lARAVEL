<?php
require __DIR__.'/vendor/autoload.php';
$app = require __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$sections = App\Models\PageSection::whereHas('page', fn ($q) => $q->where('slug', 'factsheet'))
    ->whereNull('parent_id')
    ->get(['id', 'section_key', 'title']);

foreach ($sections as $s) {
    echo "{$s->id} | {$s->section_key} | {$s->title}\n";
}
