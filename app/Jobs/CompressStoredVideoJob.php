<?php

namespace App\Jobs;

use App\Services\CompressedUploadStorage;
use App\Services\VideoCompressionTracker;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class CompressStoredVideoJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 3600;

    public int $tries = 1;

    public function __construct(
        public string $disk,
        public string $path,
    ) {}

    public function handle(): void
    {
        VideoCompressionTracker::markProcessing($this->disk, $this->path);

        $result = CompressedUploadStorage::compressStoredFile($this->disk, $this->path);

        if ($result['success'] && ($result['optimized'] ?? false)) {
            VideoCompressionTracker::markCompleted($this->disk, $this->path, $result['message']);

            return;
        }

        if ($result['success']) {
            VideoCompressionTracker::markCompleted($this->disk, $this->path, $result['message']);

            return;
        }

        VideoCompressionTracker::markFailed($this->disk, $this->path, $result['message']);
        Log::warning('Queued video compression failed.', [
            'disk' => $this->disk,
            'path' => $this->path,
            'message' => $result['message'],
        ]);
    }

    public function failed(\Throwable $exception): void
    {
        VideoCompressionTracker::markFailed($this->disk, $this->path, $exception->getMessage());
    }
}
