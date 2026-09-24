#!/bin/bash
set -e
APP="/home/sites/41b/b/ba690bc503/MOA_lARAVEL"
WEB="/home/sites/41b/b/ba690bc503/public_html"
UP="/home/sites/41b/b/ba690bc503/moa_deploy_upload"
cp "$UP/main.css" "$APP/public/assets/css/main.css"
cp "$UP/main.css" "$WEB/assets/css/main.css"
cp "$UP/frontend.blade.php" "$APP/resources/views/layout/frontend.blade.php"
cd "$APP"
php82 artisan view:clear
php82 -r "if (function_exists('opcache_reset')) opcache_reset();"
echo DONE
