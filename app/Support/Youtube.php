<?php

namespace App\Support;

class Youtube
{
    public static function videoId(?string $url): ?string
    {
        if (!$url) {
            return null;
        }

        $patterns = [
            '/youtu\.be\/([A-Za-z0-9_-]{11})/',
            '/youtube\.com\/watch\?v=([A-Za-z0-9_-]{11})/',
            '/youtube\.com\/embed\/([A-Za-z0-9_-]{11})/',
            '/youtube\.com\/shorts\/([A-Za-z0-9_-]{11})/',
            '/youtube\.com\/live\/([A-Za-z0-9_-]{11})/',
            '/youtube\.com\/v\/([A-Za-z0-9_-]{11})/',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $url, $matches)) {
                return $matches[1];
            }
        }

        parse_str((string) parse_url($url, PHP_URL_QUERY), $queryParts);
        $v = $queryParts['v'] ?? null;
        if (is_string($v) && preg_match('/^[A-Za-z0-9_-]{11}$/', $v)) {
            return $v;
        }

        return null;
    }

    public static function thumbnailUrl(?string $id, string $quality = 'hqdefault'): ?string
    {
        if (!$id) {
            return null;
        }

        return 'https://img.youtube.com/vi/'.$id.'/'.$quality.'.jpg';
    }

    public static function watchUrl(?string $url, ?string $id = null): ?string
    {
        if ($url) {
            return $url;
        }

        return $id ? 'https://www.youtube.com/watch?v='.$id : null;
    }
}
