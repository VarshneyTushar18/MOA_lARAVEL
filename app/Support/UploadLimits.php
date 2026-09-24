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

    public static function maxFileUploads(): int
    {
        $value = (int) ini_get('max_file_uploads');

        return $value > 0 ? $value : 20;
    }

    /** Safe batch size for multi-image admin uploads (leaves room for other fields). */
    public static function imageUploadBatchSize(): int
    {
        $max = self::maxFileUploads();

        return max(10, min(25, $max - 2));
    }

    /** Laravel validation rule value (KB) for a single uploaded file. */
    public static function validationMaxKilobytes(): int
    {
        $bytes = self::uploadMaxBytes();

        if ($bytes >= PHP_INT_MAX / 2) {
            return 3145728;
        }

        return max(1, (int) floor($bytes / 1024));
    }
}
