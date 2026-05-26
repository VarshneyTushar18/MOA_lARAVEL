<?php

namespace App\Support;

class UploadLimits
{
    public static function parseIniSize(string $value): int
    {
        $value = trim($value);
        if ($value === '' || $value === '-1') {
            return PHP_INT_MAX;
        }

        $unit = strtolower(substr($value, -1));
        $number = (float) $value;

        return (int) match ($unit) {
            'g' => $number * 1024 * 1024 * 1024,
            'm' => $number * 1024 * 1024,
            'k' => $number * 1024,
            default => (int) $number,
        };
    }

    public static function postMaxBytes(): int
    {
        return self::parseIniSize((string) ini_get('post_max_size'));
    }

    public static function uploadMaxBytes(): int
    {
        return self::parseIniSize((string) ini_get('upload_max_filesize'));
    }

    public static function effectiveMaxBytes(): int
    {
        return min(self::postMaxBytes(), self::uploadMaxBytes());
    }

    public static function effectiveMaxLabel(): string
    {
        $bytes = self::effectiveMaxBytes();

        if ($bytes >= 1024 * 1024) {
            return round($bytes / (1024 * 1024), 0).' MB';
        }

        return round($bytes / 1024, 0).' KB';
    }
}
