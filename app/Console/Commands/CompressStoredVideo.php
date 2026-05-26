<?php

namespace App\Console\Commands;

use App\Services\CompressedUploadStorage;
use Illuminate\Console\Command;

class CompressStoredVideo extends Command
{
    protected $signature = 'video:compress {path : Storage path relative to disk root} {--disk=public}';

    protected $description = 'Compress a video already stored on disk (background job for large uploads)';

    public function handle(): int
    {
        $disk = (string) $this->option('disk');
        $path = (string) $this->argument('path');

        $result = CompressedUploadStorage::compressStoredFile($disk, $path);

        if ($result['success']) {
            $this->info($result['message']);

            return self::SUCCESS;
        }

        $this->warn($result['message']);

        return self::FAILURE;
    }
}
