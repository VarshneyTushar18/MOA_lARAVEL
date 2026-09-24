#!/bin/bash
set -e
cp /home/sites/41b/b/ba690bc503/moa_deploy_upload/UploadLimits.php /home/sites/41b/b/ba690bc503/MOA_lARAVEL/app/Support/UploadLimits.php
cp /home/sites/41b/b/ba690bc503/moa_deploy_upload/PageSectionsController.php /home/sites/41b/b/ba690bc503/MOA_lARAVEL/app/Http/Controllers/PageSectionsController.php
cp /home/sites/41b/b/ba690bc503/moa_deploy_upload/console-upload-progress.js /home/sites/41b/b/ba690bc503/MOA_lARAVEL/public/assets/js/console-upload-progress.js
cp /home/sites/41b/b/ba690bc503/moa_deploy_upload/console-upload-progress.js /home/sites/41b/b/ba690bc503/public_html/assets/js/console-upload-progress.js
cp /home/sites/41b/b/ba690bc503/moa_deploy_upload/.user.ini /home/sites/41b/b/ba690bc503/MOA_lARAVEL/public/.user.ini
cp /home/sites/41b/b/ba690bc503/moa_deploy_upload/.user.ini /home/sites/41b/b/ba690bc503/public_html/.user.ini
cp /home/sites/41b/b/ba690bc503/moa_deploy_upload/console.blade.php /home/sites/41b/b/ba690bc503/MOA_lARAVEL/resources/views/layout/console.blade.php
cd /home/sites/41b/b/ba690bc503/MOA_lARAVEL
php82 artisan optimize:clear
php82 artisan view:cache
php82 -r "if (function_exists('opcache_reset')) opcache_reset();"
echo DONE
