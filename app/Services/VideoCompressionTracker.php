<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;

class VideoCompressionTracker
{
    private const TTL_SECONDS = 86400;

    public static function cacheKey(string $disk, string $path): string
    {
        return 'video_compress:'.md5($disk.':'.$path);
    }

    /** @return array{status: string, message?: string, updated_at?: int}|null */
    public static function get(string $disk, string $path): ?array
    {
        $state = Cache::get(self::cacheKey($disk, $path));

        return is_array($state) ? $state : null;
    }

    public static function markPending(string $disk, string $path): void
    {
        self::put([
            'status' => 'pending',
            'message' => 'Waiting to compress…',
        ], $disk, $path);
    }

    public static function markProcessing(string $disk, string $path): void
    {
        self::put([
            'status' => 'processing',
            'message' => 'Compressing video…',
        ], $disk, $path);
    }

    public static function markCompleted(string $disk, string $path, string $message): void
    {
        self::put([
            'status' => 'completed',
            'message' => $message,
        ], $disk, $path);
    }

    public static function markFailed(string $disk, string $path, string $message): void
    {
        self::put([
            'status' => 'failed',
            'message' => $message,
        ], $disk, $path);
    }

    /** @param array{status: string, message?: string} $state */
    private static function put(array $state, string $disk, string $path): void
    {
        $state['updated_at'] = time();
        Cache::put(self::cacheKey($disk, $path), $state, self::TTL_SECONDS);
    }
}
