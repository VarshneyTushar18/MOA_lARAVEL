<?php
require __DIR__.'/vendor/autoload.php';
$app = require __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Http\Controllers\GalleryController;
use App\Models\Page;

$gallery = Page::where('slug', 'gallery')->with(['sections' => fn ($q) => $q->whereNull('parent_id')->with('images')])->first();
$home = Page::where('slug', 'home')->with(['sections' => fn ($q) => $q->whereNull('parent_id')->with('images')])->first();

echo "GALLERY PAGE: ".($gallery ? $gallery->id : 'missing')."\n";
if ($gallery) {
    foreach ($gallery->sections as $s) {
        $photos = GalleryController::collectAlbumPhotos($s);
        $isSingle = GalleryController::isSingleGallerySection($s) ? 'YES' : 'no';
        echo "  id={$s->id} key={$s->section_key} single={$isSingle} title=".($s->title ?: '-')." main=".($s->image ?: 'none')." extra=".$s->images->count()." resolved=".$photos->count()."\n";
        foreach ($photos as $p) {
            echo "    photo: {$p}\n";
        }
    }
}

echo "HOME legacy gallery:\n";
if ($home) {
    $legacy = $home->sections->firstWhere('section_key', 'gallery');
    if ($legacy) {
        $legacy->loadMissing('images');
        $photos = GalleryController::collectAlbumPhotos($legacy);
        echo "  id={$legacy->id} main=".($legacy->image ?: 'none')." extra=".$legacy->images->count()." resolved=".$photos->count()."\n";
    } else {
        echo "  none\n";
    }
}

$data = GalleryController::homeTeaserData($home);
echo "TEASER singles=".$data['singles']->count()." albums=".$data['albums']->count()." preview=".($data['preview'] ?: 'none')."\n";
