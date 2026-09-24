<?php

require __DIR__.'/vendor/autoload.php';
$app = require __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$s = App\Models\PageSection::find(47);
echo json_encode([
    'id' => $s->id,
    'key' => $s->section_key,
    'title' => $s->title,
    'parent' => $s->parent_id,
    'page' => $s->page_id,
    'videos' => $s->media()->where('type', 'video')->count(),
    'images' => $s->images()->count(),
], JSON_PRETTY_PRINT).PHP_EOL;
