<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Image upload compression
    |--------------------------------------------------------------------------
    |
    | When enabled, raster images (JPEG, PNG, WebP) are re-encoded on upload
    | to save disk space. GIF uploads are left as-is to preserve animation.
    | Other file types are never passed through this pipeline.
    |
    */

    // Temporarily disabled by default; set UPLOAD_IMAGE_COMPRESSION=true in .env to re-enable.
    'enabled' => filter_var(env('UPLOAD_IMAGE_COMPRESSION', false), FILTER_VALIDATE_BOOLEAN),

    'jpeg_quality' => (int) env('UPLOAD_JPEG_QUALITY', 82),

    'png_compression' => (int) env('UPLOAD_PNG_COMPRESSION', 6),

    'webp_quality' => (int) env('UPLOAD_WEBP_QUALITY', 82),

    /*
    |--------------------------------------------------------------------------
    | Max longest edge (pixels)
    |--------------------------------------------------------------------------
    |
    | If width or height exceeds this value, the image is scaled down before
    | encoding. Set to 0 to disable resizing (quality-only compression).
    |
    */

    'max_dimension' => (int) env('UPLOAD_IMAGE_MAX_DIMENSION', 2560),

    /*
    |--------------------------------------------------------------------------
    | Video upload compression (requires ffmpeg on the server PATH)
    |--------------------------------------------------------------------------
    |
    | When enabled and ffmpeg is available, MP4/MOV/AVI uploads are re-encoded
    | to H.264/AAC. Long, already-small files (e.g. 10 min under ~25 MB) use a
    | low-bitrate profile; otherwise the file is remuxed or kept as-is.
    |
    */

    // Temporarily disabled by default; set UPLOAD_VIDEO_COMPRESSION=true in .env to re-enable.
    'video_enabled' => filter_var(env('UPLOAD_VIDEO_COMPRESSION', false), FILTER_VALIDATE_BOOLEAN),

    /*
    | Full path to ffmpeg.exe if it is not on your system PATH (common on Windows/XAMPP).
    */
    'ffmpeg_path' => env('FFMPEG_PATH'),

    'video_crf' => (int) env('UPLOAD_VIDEO_CRF', 28),

    'video_preset' => env('UPLOAD_VIDEO_PRESET', 'fast'),

    'video_audio_bitrate' => env('UPLOAD_VIDEO_AUDIO_BITRATE', '128k'),

    /*
    | Scale videos taller than this (pixels). Set 0 to disable scaling (CRF-only).
    */
    'video_max_height' => (int) env('UPLOAD_VIDEO_MAX_HEIGHT', 1080),

    /*
    |--------------------------------------------------------------------------
    | Maximum video upload size (Laravel validation)
    |--------------------------------------------------------------------------
    |
    | PHP post_max_size and upload_max_filesize must be at least this large
    | (use composer run serve or serve-large-uploads.bat for local dev).
    |
    */

    'max_video_mb' => (int) env('UPLOAD_MAX_VIDEO_MB', 200),

    'max_video_kilobytes' => (int) env('UPLOAD_MAX_VIDEO_MB', 200) * 1024,

    /*
    |--------------------------------------------------------------------------
    | Factsheet highlight clip max duration (seconds)
    |--------------------------------------------------------------------------
    |
    | Applies only to videos on the factsheet_highlights section rows.
    | Set to 0 to disable duration checks (size limit still applies).
    |
    */

    'highlight_video_max_seconds' => (int) env('UPLOAD_HIGHLIGHT_VIDEO_MAX_SECONDS', 10),

    /*
    |--------------------------------------------------------------------------
    | Production: compress in queue (recommended)
    |--------------------------------------------------------------------------
    |
    | When true (default), uploads return immediately and ffmpeg runs via
    | `php artisan queue:work`. Set QUEUE_CONNECTION=database (or redis) on
    | the server and run a supervised queue worker.
    |
    | When false, compression runs during the HTTP request (OK for local dev
    | only; large files may hit gateway timeouts in production).
    |
    */

    'video_compress_async' => filter_var(env('UPLOAD_VIDEO_COMPRESS_ASYNC', true), FILTER_VALIDATE_BOOLEAN),

    /*
    | Only used when video_compress_async=false: sync compress if file is at or
    | below this size (MB). Set 0 to always sync regardless of size.
    */
    'video_compress_sync_max_mb' => (int) env('UPLOAD_VIDEO_COMPRESS_SYNC_MAX_MB', 0),

];
