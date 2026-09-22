#!/bin/bash
APP="/home/sites/41b/b/ba690bc503/MOA_lARAVEL"
cd "$APP"
php82 artisan tinker --execute="echo 'limit=' . \App\Support\UploadLimits::effectiveMaxLabel() . PHP_EOL;"
echo "=== web php test file ==="
cat > /home/sites/41b/b/ba690bc503/public_html/_upload_limits.php <<'PHP'
<?php
header('Content-Type: text/plain');
echo 'upload_max_filesize=' . ini_get('upload_max_filesize') . "\n";
echo 'post_max_size=' . ini_get('post_max_size') . "\n";
echo 'memory_limit=' . ini_get('memory_limit') . "\n";
PHP
