<?php

require __DIR__.'/vendor/autoload.php';
$app = require __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\PageSection;
use App\Support\MediaYearResolver;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

if (! Schema::hasColumn('page_section_images', 'sort_order')) {
    fwrite(STDERR, "Run migration first: php82 artisan migrate\n");
    exit(1);
}

$yearBases = [
    2023 => 1,
    2024 => 100,
    2025 => 1000,
    2026 => 2000,
];
$yearCounters = array_fill_keys(array_keys($yearBases), 0);

function yearFromExif(string $absolute): ?int
{
    if (! is_readable($absolute)) {
        return null;
    }

    $ext = strtolower(pathinfo($absolute, PATHINFO_EXTENSION));
    if (in_array($ext, ['jpg', 'jpeg', 'tiff'], true) && function_exists('exif_read_data')) {
        $exif = @exif_read_data($absolute, 'ANY_TAG', true);
        if (is_array($exif)) {
            foreach (['DateTimeOriginal', 'DateTimeDigitized', 'DateTime'] as $key) {
                $flat = $exif[$key] ?? ($exif['EXIF'][$key] ?? null);
                if (is_string($flat) && preg_match('/^(20(2[3-6]))/', $flat, $matches)) {
                    return (int) $matches[1];
                }
            }
        }
    }

    return null;
}

$section = PageSection::findOrFail(53);
$images = $section->images()->orderBy('id')->get();

$matched = 0;
$unmatched = [];

foreach ($images as $image) {
    $absolute = Storage::disk('public')->path($image->image);
    $year = yearFromExif($absolute);

    if ($year === null) {
        $year = MediaYearResolver::fromFilename(basename($image->image));
    }

    if ($year === null || ! isset($yearBases[$year])) {
        $unmatched[] = ['id' => $image->id, 'path' => $image->image];
        continue;
    }

    $yearCounters[$year]++;
    $sortOrder = $yearBases[$year] + $yearCounters[$year] - 1;

    DB::table('page_section_images')
        ->where('id', $image->id)
        ->update(['sort_order' => $sortOrder]);

    $matched++;
}

echo "matched={$matched} unmatched=".count($unmatched)."\n";
echo 'year_counts='.json_encode($yearCounters)."\n";

if ($unmatched !== []) {
    echo "unmatched_samples:\n";
    foreach (array_slice($unmatched, 0, 10) as $row) {
        echo json_encode($row)."\n";
    }
}
