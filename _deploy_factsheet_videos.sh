#!/bin/bash
set -e
cp /home/sites/41b/b/ba690bc503/moa_deploy_upload/factsheet.blade.php /home/sites/41b/b/ba690bc503/MOA_lARAVEL/resources/views/pages/factsheet.blade.php
cd /home/sites/41b/b/ba690bc503/MOA_lARAVEL
php82 artisan view:clear
php82 -r "if (function_exists('opcache_reset')) opcache_reset();"
echo DONE
