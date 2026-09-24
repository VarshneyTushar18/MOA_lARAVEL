<?php
require __DIR__.'/vendor/autoload.php';
$app = require __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$section = App\Models\PageSection::findOrFail(53);
$images = $section->images()->orderBy('id')->get();
echo 'total_images='.$images->count()."\n";
echo 'main_image='.($section->image ?: 'none')."\n\n";

$withYear = 0;
$sample = [];
foreach ($images as $img) {
    $year = App\Support\MediaYearResolver::fromFilename($img->image);
    if ($year) {
        $withYear++;
    }
    if (count($sample) < 15) {
        $sample[] = basename($img->image).' => year='.($year ?: 'NONE').' id='.$img->id;
    }
}
echo "with_year=$withYear without=".($images->count() - $withYear)."\n\n";
echo "samples:\n".implode("\n", $sample)."\n";
