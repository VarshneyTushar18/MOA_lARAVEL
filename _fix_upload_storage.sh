#!/bin/bash
set -e
HOME_DIR="/home/sites/41b/b/ba690bc503"
APP="$HOME_DIR/MOA_lARAVEL"
PUB="$HOME_DIR/public_html"

cd "$APP"

echo "=== Before ==="
ls -la "$APP/public/storage" 2>&1 || echo "MOA public/storage missing"
ls -la "$PUB/storage" 2>&1 || echo "public_html/storage missing"
ls -ld "$APP/storage" "$APP/storage/app" "$APP/storage/app/public" 2>&1

echo "=== Fix permissions ==="
mkdir -p "$APP/storage/app/public/page_sections"
chmod -R u+rwX,g+rwX "$APP/storage" "$APP/bootstrap/cache" 2>/dev/null || chmod -R u+rwX "$APP/storage" "$APP/bootstrap/cache"

echo "=== Fix symlinks ==="
rm -f "$APP/public/storage"
ln -sfn "$APP/storage/app/public" "$APP/public/storage"
rm -f "$PUB/storage"
ln -sfn "$APP/storage/app/public" "$PUB/storage"

echo "=== storage:link ==="
php82 artisan storage:link 2>/dev/null || true

echo "=== Write test ==="
touch "$APP/storage/app/public/.upload_test" && rm -f "$APP/storage/app/public/.upload_test" && echo "write OK"

echo "=== After ==="
ls -la "$APP/public/storage"
ls -la "$PUB/storage"
php82 -r 'echo "upload=".ini_get("upload_max_filesize")." post=".ini_get("post_max_size")."\n";'
echo DONE
