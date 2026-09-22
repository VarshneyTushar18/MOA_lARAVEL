<?php

namespace App\Console\Commands;

use App\Services\CompressedUploadStorage;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class CompressStoredImages extends Command
{
    protected $signature = 'images:compress-stored
        {--disk=public : Storage disk}
        {--min-kb=400 : Only compress files larger than this (KB)}
        {--limit=0 : Max files to process (0 = all)}';

    protected $description = 'Silently compress oversized stored images in place (no UI).';

    public function handle(): int
    {
        $disk = (string) $this->option('disk');
        $minBytes = max(1, (int) $this->option('min-kb')) * 1024;
        $limit = max(0, (int) $this->option('limit'));

        $files = collect(Storage::disk($disk)->allFiles())
            ->filter(function (string $path) use ($disk, $minBytes) {
                $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
                if (! in_array($ext, ['jpg', 'jpeg', 'png', 'webp'], true)) {
                    return false;
                }

                return Storage::disk($disk)->size($path) >= $minBytes;
            })
            ->values();

        if ($limit > 0) {
            $files = $files->take($limit);
        }

        $optimized = 0;
        $skipped = 0;
        $failed = 0;

        foreach ($files as $path) {
            $before = Storage::disk($disk)->size($path);
            $result = CompressedUploadStorage::compressStoredImage($disk, $path);
            if (! ($result['success'] ?? false)) {
                $failed++;
                continue;
            }
            if ($result['optimized'] ?? false) {
                $optimized++;
                $after = Storage::disk($disk)->size($path);
                $this->line(sprintf(
                    'OK %s  %s -> %s KB',
                    $path,
                    round($before / 1024),
                    round($after / 1024)
                ));
            } else {
                $skipped++;
            }
        }

        $this->info("done optimized={$optimized} skipped={$skipped} failed={$failed}");

        return self::SUCCESS;
    }
}
