#!/bin/bash
set -e
APP="/home/sites/41b/b/ba690bc503/MOA_lARAVEL"
WEB="/home/sites/41b/b/ba690bc503/public_html"
UP="/home/sites/41b/b/ba690bc503/moa_deploy_upload"
mkdir -p "$APP/resources/views/layout" "$APP/resources/views/partials" "$APP/public/assets/js" "$WEB/assets/js"
cp "$UP/console_layout.blade.php" "$APP/resources/views/layout/console.blade.php"
cp "$UP/console_bulk_toolbar.blade.php" "$APP/resources/views/partials/console_bulk_toolbar.blade.php"
cp "$UP/console-bulk-selection.js" "$APP/public/assets/js/console-bulk-selection.js"
cp "$UP/console-bulk-selection.js" "$WEB/assets/js/console-bulk-selection.js"
cd "$APP"
php82 artisan view:clear
php82 -r "if (function_exists('opcache_reset')) opcache_reset();"
echo DONE
