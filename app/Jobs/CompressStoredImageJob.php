<?php

namespace App\Jobs;

use App\Services\CompressedUploadStorage;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class CompressStoredImageJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 300;

    public int $tries = 1;

    public function __construct(
        public string $disk,
        public string $path,
    ) {}

    public function handle(): void
    {
        $result = CompressedUploadStorage::compressStoredImage($this->disk, $this->path);

        if (! ($result['success'] ?? false)) {
            Log::warning('Queued image compression failed.', [
                'disk' => $this->disk,
                'path' => $this->path,
                'message' => $result['message'] ?? null,
            ]);
        }
    }
}
