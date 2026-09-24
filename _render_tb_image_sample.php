<?php
require __DIR__.'/vendor/autoload.php';
$app = require __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

if (! function_exists('resolveStoragePath')) {
    function resolveStoragePath($path) {
        if (! $path) {
            return null;
        }
        $disk = Illuminate\Support\Facades\Storage::disk('public');
        $normalized = ltrim(str_replace('\\', '/', $path), '/');
        if (str_starts_with($normalized, 'storage/')) {
            $normalized = substr($normalized, strlen('storage/'));
        }
        if ($disk->exists($normalized) || $disk->exists($path)) {
            return $normalized;
        }
        if (is_file(public_path('storage/'.$normalized))) {
            return $normalized;
        }

        return $path;
    }
}

$section = App\Models\PageSection::findOrFail(53);
$images = $section->images()->where('sort_order', '>=', 1000)->where('sort_order', '<', 2000)->take(3)->get();

$rendered = 0;
$skipped = 0;
foreach ($images as $img) {
    $path = $img->image;
    $resolved = resolveStoragePath($path);
    if ($resolved) {
        $rendered++;
        echo 'OK '.$path.' => /storage/'.$resolved."\n";
    } else {
        $skipped++;
        echo 'SKIP '.$path."\n";
    }
}
echo "rendered={$rendered} skipped={$skipped}\n";
