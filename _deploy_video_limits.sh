#!/bin/bash
set -e
cp /home/sites/41b/b/ba690bc503/moa_deploy_upload/.user.ini /home/sites/41b/b/ba690bc503/MOA_lARAVEL/public/.user.ini
cp /home/sites/41b/b/ba690bc503/moa_deploy_upload/.user.ini /home/sites/41b/b/ba690bc503/public_html/.user.ini
cp /home/sites/41b/b/ba690bc503/moa_deploy_upload/sections_edit.blade.php /home/sites/41b/b/ba690bc503/MOA_lARAVEL/resources/views/pages_console/sections/edit.blade.php
cp /home/sites/41b/b/ba690bc503/moa_deploy_upload/sections_add.blade.php /home/sites/41b/b/ba690bc503/MOA_lARAVEL/resources/views/pages_console/sections/add.blade.php
cd /home/sites/41b/b/ba690bc503/MOA_lARAVEL
sed -i 's/UPLOAD_MAX_VIDEO_MB=3072/UPLOAD_MAX_VIDEO_MB=0/' .env || true
grep -q '^UPLOAD_MAX_VIDEO_MB=' .env || echo 'UPLOAD_MAX_VIDEO_MB=0' >> .env
php82 artisan config:clear
php82 artisan view:clear
php82 artisan view:cache
php82 -r "if (function_exists('opcache_reset')) opcache_reset();"
echo DONE
