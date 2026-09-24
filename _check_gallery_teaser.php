<?php

require __DIR__.'/vendor/autoload.php';
$app = require __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Http\Controllers\GalleryController;
use App\Models\Page;

$home = Page::where('slug', 'home')->with('sections')->first();
$data = GalleryController::homeTeaserData($home);

echo "preview: ".($data['preview'] ?? 'none')."\n\n";

echo "SINGLES (". $data['singles']->count() ."):\n";
foreach ($data['singles'] as $i => $s) {
    echo "  [$i] {$s['path']} | {$s['title']}\n";
}

echo "\nALBUMS (". $data['albums']->count() ."):\n";
foreach ($data['albums'] as $i => $a) {
    echo "  [$i] {$a['cover']} | {$a['title']}\n";
}
