<?php

namespace App\Services;

use GdImage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CompressedUploadStorage
{
    public static function storeImage(UploadedFile $file, string $directory, string $disk = 'public'): string
    {
        if (! self::shouldProcess($file)) {
            return $file->store($directory, $disk);
        }

        $dir = trim($directory, '/');
        $path = $dir.'/'.Str::random(40).'.'.self::targetExtension($file);

        return self::tryWriteCompressed($file, $path, $disk)
            ? $path
            : $file->store($directory, $disk);
    }

    public static function storeImageAs(UploadedFile $file, string $directory, string $filename, string $disk = 'local'): string
    {
        if (! self::shouldProcess($file)) {
            return $file->storeAs($directory, $filename, $disk);
        }

        $dir = trim($directory, '/');
        $base = pathinfo($filename, PATHINFO_FILENAME);
        $path = $dir.'/'.$base.'.'.self::targetExtension($file);

        return self::tryWriteCompressed($file, $path, $disk)
            ? $path
            : $file->storeAs($directory, $filename, $disk);
    }

    private static function shouldProcess(UploadedFile $file): bool
    {
        if (! extension_loaded('gd')) {
            return false;
        }

        if (! config('upload_compression.enabled', true)) {
            return false;
        }

        $mime = (string) $file->getMimeType();
        if ($mime === 'image/gif') {
            return false;
        }

        return in_array($mime, ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'], true);
    }

    private static function targetExtension(UploadedFile $file): string
    {
        return match ($file->getMimeType()) {
            'image/jpeg', 'image/jpg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
            default => strtolower((string) $file->getClientOriginalExtension()) ?: 'jpg',
        };
    }

    private static function tryWriteCompressed(UploadedFile $file, string $path, string $disk): bool
    {
        $realPath = $file->getRealPath();
        if (! $realPath || ! is_readable($realPath)) {
            return false;
        }

        $mime = (string) $file->getMimeType();
        $img = self::createFromFile($realPath, $mime);
        if (! $img instanceof GdImage) {
            return false;
        }

        try {
            $maxDim = (int) config('upload_compression.max_dimension', 2560);
            $img = self::maybeDownscale($img, $maxDim);
            if (! $img instanceof GdImage) {
                return false;
            }

            $binary = self::encode($img, $mime);
            if ($binary === null) {
                return false;
            }

            return Storage::disk($disk)->put($path, $binary);
        } finally {
            if (isset($img) && $img instanceof GdImage) {
                imagedestroy($img);
            }
        }
    }

    /**
     * @return GdImage|false
     */
    private static function createFromFile(string $realPath, string $mime)
    {
        return match ($mime) {
            'image/jpeg', 'image/jpg' => @imagecreatefromjpeg($realPath),
            'image/png' => self::createFromPng($realPath),
            'image/webp' => function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($realPath) : false,
            default => false,
        };
    }

    /**
     * @return GdImage|false
     */
    private static function createFromPng(string $realPath)
    {
        $im = @imagecreatefrompng($realPath);
        if ($im instanceof GdImage) {
            imagealphablending($im, false);
            imagesavealpha($im, true);
        }

        return $im;
    }

    /**
     * @return GdImage|false
     */
    private static function maybeDownscale(GdImage $src, int $maxDim)
    {
        if ($maxDim <= 0) {
            return $src;
        }

        $w = imagesx($src);
        $h = imagesy($src);
        if ($w <= 0 || $h <= 0) {
            imagedestroy($src);

            return false;
        }

        if ($w <= $maxDim && $h <= $maxDim) {
            return $src;
        }

        $ratio = min($maxDim / $w, $maxDim / $h);
        $nw = max(1, (int) round($w * $ratio));
        $nh = max(1, (int) round($h * $ratio));

        $scaled = imagescale($src, $nw, $nh, IMG_BILINEAR_FIXED);
        if ($scaled === false) {
            return $src;
        }

        imagedestroy($src);

        return $scaled;
    }

    private static function encode(GdImage $src, string $mime): ?string
    {
        ob_start();
        $ok = false;

        if ($mime === 'image/jpeg' || $mime === 'image/jpg') {
            $ok = imagejpeg($src, null, (int) config('upload_compression.jpeg_quality', 82));
        } elseif ($mime === 'image/png') {
            imagealphablending($src, false);
            imagesavealpha($src, true);
            $ok = imagepng($src, null, (int) config('upload_compression.png_compression', 6));
        } elseif ($mime === 'image/webp' && function_exists('imagewebp')) {
            $ok = imagewebp($src, null, (int) config('upload_compression.webp_quality', 82));
        }

        $binary = ob_get_clean();

        return $ok && $binary !== false && $binary !== '' ? $binary : null;
    }
}
