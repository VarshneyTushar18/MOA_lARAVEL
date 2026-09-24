<?php
require __DIR__.'/vendor/autoload.php';
$app = require __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\PageSection;
use App\Support\MediaYearResolver;
use Illuminate\Support\Facades\Schema;

echo 'sort_order_column='.(Schema::hasColumn('page_section_images', 'sort_order') ? 'yes' : 'no')."\n";

$section = PageSection::findOrFail(53);
$byYear = [2023 => 0, 2024 => 0, 2025 => 0, 2026 => 0, 'none' => 0];
$withSort = 0;

foreach ($section->images as $img) {
    if ((int) ($img->sort_order ?? 0) > 0) {
        $withSort++;
    }
    $year = MediaYearResolver::fromImage($img);
    if ($year && isset($byYear[$year])) {
        $byYear[$year]++;
    } else {
        $byYear['none']++;
    }
}

echo 'total_images='.$section->images->count()."\n";
echo 'with_sort_order='.$withSort."\n";
echo 'by_year='.json_encode($byYear)."\n";

$byYearVideos = [2023 => 0, 2024 => 0, 2025 => 0, 2026 => 0];
foreach ($section->media->where('type', 'video') as $video) {
    $year = MediaYearResolver::fromVideo($video);
    if ($year) {
        $byYearVideos[$year]++;
    }
}
echo 'videos_by_year='.json_encode($byYearVideos)."\n";
