<?php
header('Content-Type: text/plain');
$home = '/home/sites/41b/b/ba690bc503';
require $home.'/MOA_lARAVEL/vendor/autoload.php';
$app = require $home.'/MOA_lARAVEL/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo 'web_upload='.ini_get('upload_max_filesize')."\n";
echo 'web_post='.ini_get('post_max_size')."\n";
echo 'web_memory='.ini_get('memory_limit')."\n";
echo 'web_max_input_time='.ini_get('max_input_time')."\n";
echo 'web_max_execution_time='.ini_get('max_execution_time')."\n";
echo 'effective='.App\Support\UploadLimits::effectiveMaxLabel()."\n";
echo 'effective_bytes='.App\Support\UploadLimits::effectiveMaxBytes()."\n";
echo 'UPLOAD_MAX_VIDEO_MB='.config('upload_compression.max_video_mb')."\n";
