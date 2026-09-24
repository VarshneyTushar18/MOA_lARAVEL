<?php

require __DIR__.'/vendor/autoload.php';
$app = require __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\PageSection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

$manifestPath = $argv[1] ?? __DIR__.'/_tb_pledge_image_manifest.json';
if (! is_readable($manifestPath)) {
    fwrite(STDERR, "Manifest not found: {$manifestPath}\n");
    exit(1);
}

if (! Schema::hasColumn('page_section_images', 'sort_order')) {
    fwrite(STDERR, "Run migration first: php82 artisan migrate\n");
    exit(1);
}

/** @var array<int, array{md5: string, size: int, year: int, name: string}> $manifest */
$manifest = json_decode(file_get_contents($manifestPath), true, 512, JSON_THROW_ON_ERROR);

$byMd5 = [];
$bySize = [];
foreach ($manifest as $entry) {
    $byMd5[$entry['md5']] = (int) $entry['year'];
    $size = (int) $entry['size'];
    $bySize[$size][] = (int) $entry['year'];
}

$yearBases = [
    2023 => 1,
    2024 => 100,
    2025 => 1000,
    2026 => 2000,
];
$yearCounters = array_fill_keys(array_keys($yearBases), 0);

$section = PageSection::findOrFail(53);
$images = $section->images()->orderBy('id')->get();

$matched = 0;
$unmatched = [];

foreach ($images as $image) {
    $path = $image->image;
    $absolute = Storage::disk('public')->path($path);
    if (! is_readable($absolute)) {
        $unmatched[] = ['id' => $image->id, 'reason' => 'missing file', 'path' => $path];
        continue;
    }

    $md5 = md5_file($absolute);
    $size = filesize($absolute) ?: 0;
    $year = $byMd5[$md5] ?? null;

    if ($year === null && isset($bySize[$size]) && count($bySize[$size]) === 1) {
        $year = $bySize[$size][0];
    }

    if ($year === null) {
        $absolute = Storage::disk('public')->path($path);
        if (is_readable($absolute) && function_exists('exif_read_data')) {
            $ext = strtolower(pathinfo($absolute, PATHINFO_EXTENSION));
            if (in_array($ext, ['jpg', 'jpeg', 'tiff'], true)) {
                $exif = @exif_read_data($absolute, 'ANY_TAG', true);
                if (is_array($exif)) {
                    foreach (['DateTimeOriginal', 'DateTimeDigitized', 'DateTime'] as $key) {
                        $flat = $exif[$key] ?? ($exif['EXIF'][$key] ?? null);
                        if (is_string($flat) && preg_match('/^(20(2[3-6]))/', $flat, $matches)) {
                            $year = (int) $matches[1];
                            break;
                        }
                    }
                }
            }
        }
    }

    if ($year === null || ! isset($yearBases[$year])) {
        $unmatched[] = ['id' => $image->id, 'reason' => 'no year match', 'md5' => $md5, 'size' => $size];
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
