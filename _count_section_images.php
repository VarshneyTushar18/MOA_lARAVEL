<?php
require __DIR__.'/vendor/autoload.php';
$app = require __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

foreach ([45, 44] as $id) {
    $section = App\Models\PageSection::withCount('images')->find($id);
    if ($section) {
        echo "section {$section->id} ({$section->section_key}): {$section->images_count} images\n";
    }
}
