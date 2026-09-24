#!/bin/bash
set -e
APP="/home/sites/41b/b/ba690bc503/MOA_lARAVEL"
PUB="/home/sites/41b/b/ba690bc503/public_html"
UP="/home/sites/41b/b/ba690bc503/moa_deploy_upload"
mkdir -p "$APP/app/Services" "$APP/resources/views/layout" "$APP/resources/views/partials" "$APP/public/assets/css" "$PUB/assets/css"
cp "$UP/FooterContentService.php" "$APP/app/Services/FooterContentService.php"
cp "$UP/frontend.blade.php" "$APP/resources/views/layout/frontend.blade.php"
cp "$UP/site-footer.blade.php" "$APP/resources/views/partials/site-footer.blade.php"
cp "$UP/main.css" "$APP/public/assets/css/main.css"
cp "$UP/main.css" "$PUB/assets/css/main.css"
cd "$APP"
php82 artisan view:clear
php82 -r "if (function_exists('opcache_reset')) opcache_reset();"
echo DONE
