<?php

namespace App\Support;

use App\Models\PageSectionImage;
use App\Models\PageSectionMedia;

class MediaYearResolver
{
    /** @var array<int, int> */
    private const SORT_YEAR_MAP = [
        2023 => [1, 99],
        2024 => [100, 999],
        2025 => [1000, 1999],
        2026 => [2000, 9999],
    ];

    public static function fromFilename(?string $name, ?int $sortOrder = null): ?int
    {
        if ($name) {
            $basename = basename(str_replace('\\', '/', $name));

            $patterns = [
                '/\b(202[3-6])\b/',
                '/^(202[3-6])\d{4,}/',
                '/(?:^|[^0-9])(202[3-6])(\d{2})(\d{2})(?:[^0-9]|$)/',
                '/VID[_-](202[3-6])/i',
                '/IMG[_-](202[3-6])/i',
                '/(?:WhatsApp\s+(?:Image|Video)\s+)(202[3-6])-/i',
            ];

            foreach ($patterns as $pattern) {
                if (preg_match($pattern, $basename, $matches)) {
                    return (int) $matches[1];
                }
            }
        }

        return self::fromSortOrder($sortOrder);
    }

    public static function fromVideo(PageSectionMedia $media): ?int
    {
        $label = $media->title ?: $media->file_path;

        return self::fromFilename($label, (int) ($media->sort_order ?? 0));
    }

    public static function fromImage(PageSectionImage $image): ?int
    {
        return self::fromFilename($image->image, (int) ($image->sort_order ?? 0));
    }

    public static function fromSortOrder(?int $sortOrder): ?int
    {
        if ($sortOrder === null || $sortOrder <= 0) {
            return null;
        }

        foreach (self::SORT_YEAR_MAP as $year => [$min, $max]) {
            if ($sortOrder >= $min && $sortOrder <= $max) {
                return $year;
            }
        }

        return null;
    }

    /**
     * @param  iterable<int>  $years
     * @return array<int, array{images: \Illuminate\Support\Collection, videos: \Illuminate\Support\Collection}>
     */
    public static function emptyYearBuckets(array $years): array
    {
        $buckets = [];
        foreach ($years as $year) {
            $buckets[$year] = [
                'images' => collect(),
                'videos' => collect(),
            ];
        }

        return $buckets;
    }
}
