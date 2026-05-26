<?php

namespace App\Services;

use App\Jobs\CompressStoredVideoJob;
use GdImage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CompressedUploadStorage
{
    /** @var array{applied: bool, reason: string|null} */
    private static array $lastVideoCompression = [
        'applied' => false,
        'reason' => null,
    ];
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

    /**
     * Store a video upload, optionally re-encoding with ffmpeg when available.
     */
    public static function storeVideo(UploadedFile $file, string $directory, string $disk = 'public'): string
    {
        self::$lastVideoCompression = ['applied' => false, 'reason' => null];

        $path = $file->store($directory, $disk);

        if (! config('upload_compression.video_enabled', true)) {
            self::$lastVideoCompression['reason'] = 'disabled';

            return $path;
        }

        if (! self::resolveFfmpegBinary()) {
            self::$lastVideoCompression['reason'] = 'ffmpeg_missing';
            Log::warning('Video stored without compression: ffmpeg not found. Install ffmpeg or set FFMPEG_PATH in .env.');

            return $path;
        }

        if (! self::isCompressibleVideo($file)) {
            self::$lastVideoCompression['reason'] = 'unsupported_type';

            return $path;
        }

        if (self::shouldCompressAsync($file)) {
            VideoCompressionTracker::markPending($disk, $path);
            CompressStoredVideoJob::dispatch($disk, $path);

            self::$lastVideoCompression['reason'] = 'compressing_in_background';

            Log::info('Video stored; compression queued.', [
                'path' => $path,
                'size_mb' => round($file->getSize() / (1024 * 1024), 1),
            ]);

            return $path;
        }

        return self::compressImmediately($disk, $path, $file->getSize());
    }

    private static function shouldCompressAsync(UploadedFile $file): bool
    {
        if (! config('upload_compression.video_compress_async', true)) {
            return false;
        }

        $syncMaxMb = (int) config('upload_compression.video_compress_sync_max_mb', 0);
        if ($syncMaxMb <= 0) {
            return true;
        }

        return $file->getSize() > $syncMaxMb * 1024 * 1024;
    }

    private static function compressImmediately(string $disk, string $path, int $originalBytes): string
    {
        if (function_exists('set_time_limit')) {
            @set_time_limit(0);
        }

        Log::info('Video stored; compressing during request (sync mode).', [
            'path' => $path,
            'size_mb' => round($originalBytes / (1024 * 1024), 1),
        ]);

        $result = self::compressStoredFile($disk, $path);

        if ($result['success'] && ($result['optimized'] ?? false)) {
            self::$lastVideoCompression = ['applied' => true, 'reason' => null];
        } elseif ($result['success']) {
            self::$lastVideoCompression['reason'] = 'encode_failed_or_not_smaller';
        } else {
            self::$lastVideoCompression['reason'] = 'encode_failed_or_not_smaller';
            Log::warning('Video compression failed after upload.', [
                'path' => $path,
                'message' => $result['message'],
            ]);
        }

        return $path;
    }

    /**
     * @return array{success: bool, message: string, optimized?: bool}
     */
    public static function compressStoredFile(string $disk, string $relativePath): array
    {
        $ffmpeg = self::resolveFfmpegBinary();
        if (! $ffmpeg) {
            return ['success' => false, 'message' => 'ffmpeg not found'];
        }

        if (! Storage::disk($disk)->exists($relativePath)) {
            return ['success' => false, 'message' => "File not found: {$relativePath}"];
        }

        $absolutePath = Storage::disk($disk)->path($relativePath);

        if (function_exists('set_time_limit')) {
            @set_time_limit(0);
        }

        $originalSize = (int) filesize($absolutePath);
        $duration = self::probeVideoDuration($absolutePath);
        $profile = self::buildVideoEncodeProfile($originalSize, $duration);

        $tempMp4 = self::tempMp4Path();
        if ($tempMp4 === null) {
            return ['success' => false, 'message' => 'Could not create temp file'];
        }

        if (self::runFfmpegEncode($ffmpeg, $absolutePath, $tempMp4, $profile)) {
            $encodedSize = (int) filesize($tempMp4);

            if (self::shouldUseEncodedOutput($originalSize, $encodedSize)) {
                Storage::disk($disk)->put($relativePath, (string) file_get_contents($tempMp4));
                @unlink($tempMp4);

                Log::info('Video compression completed.', [
                    'path' => $relativePath,
                    'from_mb' => round($originalSize / (1024 * 1024), 1),
                    'to_mb' => round($encodedSize / (1024 * 1024), 1),
                    'duration_sec' => $duration,
                    'profile' => $profile,
                ]);

                return [
                    'success' => true,
                    'optimized' => true,
                    'message' => sprintf(
                        'Compressed %.1f MB → %.1f MB',
                        $originalSize / (1024 * 1024),
                        $encodedSize / (1024 * 1024)
                    ),
                ];
            }
        }

        @unlink($tempMp4);

        $remuxPath = self::tempMp4Path();
        if ($remuxPath !== null && self::runFfmpegRemuxFaststart($ffmpeg, $absolutePath, $remuxPath)) {
            $remuxSize = (int) filesize($remuxPath);

            if ($remuxSize > 0 && $remuxSize <= $originalSize) {
                Storage::disk($disk)->put($relativePath, (string) file_get_contents($remuxPath));
                @unlink($remuxPath);

                return [
                    'success' => true,
                    'optimized' => true,
                    'message' => sprintf(
                        'Optimized for web playback (%.1f MB → %.1f MB)',
                        $originalSize / (1024 * 1024),
                        $remuxSize / (1024 * 1024)
                    ),
                ];
            }

            @unlink($remuxPath);
        }

        Log::info('Video compression kept original (encode would increase size).', [
            'path' => $relativePath,
            'size_mb' => round($originalSize / (1024 * 1024), 1),
            'duration_sec' => $duration,
        ]);

        return [
            'success' => true,
            'optimized' => false,
            'message' => 'Kept original file (already highly compressed for its length).',
        ];
    }

    public static function videoCompressionNotice(): ?string
    {
        if (self::$lastVideoCompression['applied']) {
            return 'Video saved and compressed. Download from the site will use the optimized file.';
        }

        return match (self::$lastVideoCompression['reason']) {
            'ffmpeg_missing' => 'Video saved. Compression skipped — install ffmpeg on the server and set FFMPEG_PATH in .env.',
            'disabled' => 'Video saved at full size — UPLOAD_VIDEO_COMPRESSION is disabled.',
            'compressing_in_background' => 'Video saved. Compression is running in the queue (usually 2–20 min). Refresh later before downloading; the file may still be full size until processing finishes.',
            'encode_failed_or_not_smaller' => 'Video saved at full size — already highly compressed for its length (re-encoding would not help).',
            default => null,
        };
    }

    private static function isCompressibleVideo(UploadedFile $file): bool
    {
        $mime = (string) $file->getMimeType();

        return str_starts_with($mime, 'video/')
            || in_array(strtolower((string) $file->getClientOriginalExtension()), ['mp4', 'mov', 'avi'], true);
    }

    /**
     * @return array{maxHeight: int, crf: int, maxrateKbps: int|null, audio: string}
     */
    private static function buildVideoEncodeProfile(int $originalSize, ?float $duration): array
    {
        $crf = max(18, min(40, (int) config('upload_compression.video_crf', 28)));
        $preset = preg_replace('/[^a-z0-9_-]/i', '', (string) config('upload_compression.video_preset', 'fast')) ?: 'fast';
        $audioBitrate = preg_replace('/[^0-9kKmM]/', '', (string) config('upload_compression.video_audio_bitrate', '128k')) ?: '128k';
        $maxHeight = max(0, (int) config('upload_compression.video_max_height', 1080));

        $profile = [
            'maxHeight' => $maxHeight,
            'crf' => $crf,
            'maxrateKbps' => null,
            'audio' => $audioBitrate,
            'preset' => $preset,
        ];

        if ($originalSize > 80 * 1024 * 1024) {
            $profile['crf'] = max($crf, 32);
            $profile['maxrateKbps'] = 2500;
            $profile['audio'] = '96k';

            return $profile;
        }

        $bitrateKbps = ($duration !== null && $duration > 0)
            ? ($originalSize * 8) / $duration / 1000
            : 0;

        // Long videos that are already small on disk (e.g. 10 min under ~25 MB).
        if ($duration !== null && $duration >= 120 && $bitrateKbps > 0 && $bitrateKbps < 700) {
            $profile['maxHeight'] = min($maxHeight > 0 ? $maxHeight : 720, 720);
            $profile['crf'] = max($crf, 32);
            $profile['maxrateKbps'] = max(280, (int) floor($bitrateKbps * 0.85));
            $profile['audio'] = '96k';
        }

        return $profile;
    }

    private static function shouldUseEncodedOutput(int $originalSize, int $encodedSize): bool
    {
        if ($encodedSize <= 0) {
            return false;
        }

        if ($encodedSize <= $originalSize) {
            return true;
        }

        // Allow a tiny increase when re-encoding already-heavy files for web compatibility.
        return $encodedSize <= (int) ceil($originalSize * 1.05);
    }

    private static function tempMp4Path(): ?string
    {
        $tempBase = tempnam(sys_get_temp_dir(), 'moa_vid_');
        if ($tempBase === false) {
            return null;
        }

        $tempMp4 = $tempBase.'.mp4';
        @unlink($tempBase);

        return $tempMp4;
    }

    /**
     * @param  array{maxHeight: int, crf: int, maxrateKbps: int|null, audio: string, preset: string}  $profile
     */
    private static function runFfmpegEncode(string $ffmpeg, string $inputPath, string $outputPath, array $profile): bool
    {
        if (! is_readable($inputPath)) {
            return false;
        }

        $videoFilter = $profile['maxHeight'] > 0
            ? sprintf('-vf scale=-2:%d', $profile['maxHeight'])
            : '';

        $rateLimit = '';
        if (! empty($profile['maxrateKbps'])) {
            $rateLimit = sprintf(
                ' -maxrate %dk -bufsize %dk',
                $profile['maxrateKbps'],
                $profile['maxrateKbps'] * 2
            );
        }

        $command = sprintf(
            '%s -y -i %s %s -c:v libx264 -crf %d -preset %s%s -c:a aac -b:a %s -movflags +faststart %s 2>NUL',
            escapeshellarg($ffmpeg),
            escapeshellarg($inputPath),
            $videoFilter,
            $profile['crf'],
            $profile['preset'],
            $rateLimit,
            $profile['audio'],
            escapeshellarg($outputPath)
        );

        if (DIRECTORY_SEPARATOR !== '\\') {
            $command = str_replace(' 2>NUL', ' 2>/dev/null', $command);
        }

        shell_exec($command);

        return is_file($outputPath) && filesize($outputPath) > 0;
    }

    private static function runFfmpegRemuxFaststart(string $ffmpeg, string $inputPath, string $outputPath): bool
    {
        if (! is_readable($inputPath)) {
            return false;
        }

        $command = sprintf(
            '%s -y -i %s -c copy -movflags +faststart %s 2>NUL',
            escapeshellarg($ffmpeg),
            escapeshellarg($inputPath),
            escapeshellarg($outputPath)
        );

        if (DIRECTORY_SEPARATOR !== '\\') {
            $command = str_replace(' 2>NUL', ' 2>/dev/null', $command);
        }

        shell_exec($command);

        return is_file($outputPath) && filesize($outputPath) > 0;
    }

    private static function probeVideoDuration(string $absolutePath): ?float
    {
        $ffprobe = self::resolveFfprobeBinary();
        if (! $ffprobe || ! is_readable($absolutePath)) {
            return null;
        }

        $command = sprintf(
            '%s -v error -show_entries format=duration -of default=noprint_wrappers=1:nokey=1 %s 2>NUL',
            escapeshellarg($ffprobe),
            escapeshellarg($absolutePath)
        );

        if (DIRECTORY_SEPARATOR !== '\\') {
            $command = str_replace(' 2>NUL', ' 2>/dev/null', $command);
        }

        $output = trim((string) shell_exec($command));

        return ($output !== '' && is_numeric($output)) ? (float) $output : null;
    }

    private static function resolveFfprobeBinary(): ?string
    {
        $ffmpeg = self::resolveFfmpegBinary();
        if (! $ffmpeg) {
            return null;
        }

        $candidate = dirname($ffmpeg).DIRECTORY_SEPARATOR.(DIRECTORY_SEPARATOR === '\\' ? 'ffprobe.exe' : 'ffprobe');

        return is_file($candidate) ? $candidate : null;
    }

    private static function resolveFfmpegBinary(): ?string
    {
        static $cached = null;

        if ($cached === false) {
            return null;
        }

        if (is_string($cached) && $cached !== '') {
            return $cached;
        }

        $configured = trim((string) config('upload_compression.ffmpeg_path', ''));
        if ($configured !== '' && is_file($configured)) {
            $cached = $configured;

            return $cached;
        }

        $candidates = [];

        if (DIRECTORY_SEPARATOR === '\\') {
            $where = trim((string) shell_exec('where ffmpeg 2>NUL'));
            if ($where !== '') {
                foreach (preg_split("/\r\n|\n|\r/", $where) as $line) {
                    $candidates[] = trim($line);
                }
            }

            $winGetRoot = getenv('LOCALAPPDATA')
                ? getenv('LOCALAPPDATA').'\\Microsoft\\WinGet\\Packages'
                : '';
            if ($winGetRoot !== '' && is_dir($winGetRoot)) {
                $matches = glob($winGetRoot.'\\Gyan.FFmpeg_*\\ffmpeg-*\\bin\\ffmpeg.exe') ?: [];
                $candidates = array_merge($candidates, $matches);
            }

            $candidates = array_merge($candidates, [
                'C:\\ffmpeg\\bin\\ffmpeg.exe',
                'C:\\Program Files\\ffmpeg\\bin\\ffmpeg.exe',
                'C:\\Program Files (x86)\\ffmpeg\\bin\\ffmpeg.exe',
            ]);
        } else {
            $which = trim((string) shell_exec('command -v ffmpeg 2>/dev/null'));
            if ($which !== '') {
                $candidates[] = $which;
            }
            $candidates[] = '/usr/bin/ffmpeg';
            $candidates[] = '/usr/local/bin/ffmpeg';
        }

        foreach ($candidates as $candidate) {
            if ($candidate !== '' && is_file($candidate)) {
                $cached = $candidate;

                return $cached;
            }
        }

        $cached = false;

        return null;
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
