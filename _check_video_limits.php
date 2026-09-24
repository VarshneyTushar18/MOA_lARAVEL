<?php

require __DIR__.'/vendor/autoload.php';
$app = require __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo 'UPLOAD_MAX_VIDEO_MB='.config('upload_compression.max_video_mb')."\n";
echo 'UPLOAD_VIDEO_COMPRESSION='.(config('upload_compression.video_enabled') ? 'true' : 'false')."\n";
echo 'UPLOAD_IMAGE_COMPRESSION='.(config('upload_compression.enabled') ? 'true' : 'false')."\n";
echo 'php_upload='.ini_get('upload_max_filesize')."\n";
echo 'php_post='.ini_get('post_max_size')."\n";
echo 'effective='.App\Support\UploadLimits::effectiveMaxLabel()."\n";

$configuredMb = (int) config('upload_compression.max_video_mb', 0);
$phpKb = max(1, (int) floor(App\Support\UploadLimits::effectiveMaxBytes() / 1024));
$maxKb = $configuredMb <= 0 ? $phpKb : min($configuredMb * 1024, $phpKb);
echo 'laravel_video_max_mb='.floor($maxKb / 1024)."\n";
