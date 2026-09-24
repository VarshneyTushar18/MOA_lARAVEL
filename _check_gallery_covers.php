<?php

require __DIR__.'/vendor/autoload.php';
$app = require __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Http\Controllers\GalleryController;
use App\Models\Page;
use Illuminate\Support\Facades\Storage;

$page = Page::where('slug', 'gallery')->first();
if (! $page) {
    echo "No gallery page\n";
    exit(1);
}

foreach ($page->sections()->whereNull('parent_id')->with('images')->orderBy('sort_order')->get() as $section) {
    $cover = GalleryController::albumCover($section);
    $photos = GalleryController::collectAlbumPhotos($section);
    echo $section->id.' | '.$section->section_key.' | '.$section->title."\n";
    echo '  photos ('.$photos->count().'): '.implode(', ', $photos->all())."\n\n";
}
