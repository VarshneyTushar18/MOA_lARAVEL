<?php

require __DIR__.'/vendor/autoload.php';
$app = require __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Page;
use App\Models\PageSection;

$page = Page::where('slug', 'best_practices')->firstOrFail();
$sections = PageSection::query()
    ->where('page_id', $page->id)
    ->where('section_key', 'patient_medicine_photos')
    ->orderBy('id')
    ->get();

echo 'found='.$sections->count()."\n";

if ($sections->count() <= 1) {
    echo "nothing_to_merge\n";
    exit(0);
}

$primary = $sections->first();
$mergedImages = 0;
$mergedMedia = 0;

foreach ($sections->skip(1) as $duplicate) {
    $mergedImages += $duplicate->images()->count();
    $mergedMedia += $duplicate->media()->count();

    $duplicate->images()->update(['page_section_id' => $primary->id]);
    $duplicate->media()->update(['page_section_id' => $primary->id]);
    $duplicate->delete();
}

$primary->title = 'Patient Medicine Distribution';
$primary->save();

echo "primary_id={$primary->id}\n";
echo 'images='.$primary->images()->count()."\n";
echo 'media='.$primary->media()->count()."\n";
echo "merged_duplicates=".($sections->count() - 1)."\n";
