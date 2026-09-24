#!/bin/bash
set -e
cp /home/sites/41b/b/ba690bc503/moa_deploy_upload/.user.ini /home/sites/41b/b/ba690bc503/MOA_lARAVEL/public/.user.ini
cp /home/sites/41b/b/ba690bc503/moa_deploy_upload/.user.ini /home/sites/41b/b/ba690bc503/public_html/.user.ini
# StackCP merges ;+StackCP block — append only our overrides after EOF marker
if ! grep -q ';+Unmarked' /home/sites/41b/b/ba690bc503/public_html/.user.ini 2>/dev/null; then
  cat >> /home/sites/41b/b/ba690bc503/public_html/.user.ini <<'INI'

;+Unmarked
upload_max_filesize = 12800M
post_max_size = 12800M
max_file_uploads = 1000
max_input_vars = 10000
memory_limit = 2048M
max_execution_time = 0
max_input_time = 0
;-Unmarked
INI
fi
cd /home/sites/41b/b/ba690bc503/MOA_lARAVEL
sed -i 's/^UPLOAD_MAX_VIDEO_MB=.*/UPLOAD_MAX_VIDEO_MB=0/' .env || true
grep -q '^UPLOAD_MAX_VIDEO_MB=' .env || echo 'UPLOAD_MAX_VIDEO_MB=0' >> .env
php82 artisan config:clear
php82 artisan view:clear
php82 artisan view:cache
php82 -r "if (function_exists('opcache_reset')) opcache_reset();"
echo DONE
