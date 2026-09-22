#!/bin/bash
cd ~/MOA_lARAVEL || exit 1
echo "=== PHP limits ==="
php82 -r 'echo "upload_max=".ini_get("upload_max_filesize")." post_max=".ini_get("post_max_size")."\n";'
echo "=== storage link ==="
ls -la public/storage
echo "=== disk ==="
df -h .
echo "=== recent errors ==="
grep -E "ERROR|Upload|validation|Permission|failed|PostTooLarge|disk" storage/logs/laravel.log | tail -25
