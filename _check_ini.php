<?php

require __DIR__.'/vendor/autoload.php';
$app = require __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo 'cli post_max='.ini_get('post_max_size')."\n";
echo 'cli upload_max='.ini_get('upload_max_filesize')."\n";
echo 'effective='.App\Support\UploadLimits::effectiveMaxLabel()."\n";
