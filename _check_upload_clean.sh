#!/bin/bash
set -e
APP="/home/sites/41b/b/ba690bc503/MOA_lARAVEL"
PUB="/home/sites/41b/b/ba690bc503/public_html"
cd "$APP"
echo "=== PHP web limits (php82) ==="
php82 -r 'echo "upload=".ini_get("upload_max_filesize")." post=".ini_get("post_max_size")."\n";'
echo "=== MOA public/storage ==="
ls -la "$APP/public/storage" || echo "MISSING"
echo "=== public_html/storage ==="
ls -la "$PUB/storage" || echo "MISSING"
echo "=== disk ==="
df -h "$APP"
echo "=== storage writable ==="
test -w "$APP/storage/app/public" && echo "writable" || echo "NOT writable"
echo "=== recent errors ==="
if [ -f storage/logs/laravel.log ]; then
  grep -a "local.ERROR\|PostTooLarge\|ValidationException\|Permission denied" storage/logs/laravel.log | tail -8 || true
else
  echo "no laravel.log"
fi
