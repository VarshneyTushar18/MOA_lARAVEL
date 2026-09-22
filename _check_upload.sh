#!/bin/bash
cd ~/MOA_lARAVEL || exit 1
php82 -r 'echo "upload_max_filesize=".ini_get("upload_max_filesize")."\n"; echo "post_max_size=".ini_get("post_max_size")."\n"; echo "memory_limit=".ini_get("memory_limit")."\n";'
echo "--- storage permissions ---"
ls -ld storage storage/app storage/app/public 2>&1
echo "--- public/storage link ---"
ls -la public/storage 2>&1 | head -3
echo "--- disk ---"
df -h . 2>&1
echo "--- recent log ---"
tail -40 storage/logs/laravel.log 2>/dev/null || echo "no log"
echo "--- test write ---"
touch storage/app/public/.upload_test 2>&1 && rm -f storage/app/public/.upload_test && echo "write OK" || echo "write FAILED"
