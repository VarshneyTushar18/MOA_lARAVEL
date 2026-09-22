#!/bin/bash
set -e
APP="/home/sites/41b/b/ba690bc503/MOA_lARAVEL"
PUB="/home/sites/41b/b/ba690bc503/public_html"
UP="/home/sites/41b/b/ba690bc503/moa_deploy_upload"
mkdir -p "$APP/app/Http/Controllers" "$APP/resources/views/pages" "$APP/resources/views/pages_console/sections" "$APP/public/assets/css" "$PUB/assets/css"
cp "$UP/GalleryController.php" "$APP/app/Http/Controllers/GalleryController.php"
cp "$UP/PageSectionsController.php" "$APP/app/Http/Controllers/PageSectionsController.php"
cp "$UP/home.blade.php" "$APP/resources/views/pages/home.blade.php"
cp "$UP/sections_list.blade.php" "$APP/resources/views/pages_console/sections/list.blade.php"
cp "$UP/sections_add.blade.php" "$APP/resources/views/pages_console/sections/add.blade.php"
cp "$UP/sections_edit.blade.php" "$APP/resources/views/pages_console/sections/edit.blade.php"
cp "$UP/main.css" "$APP/public/assets/css/main.css"
cp "$UP/main.css" "$PUB/assets/css/main.css"
cd "$APP"
php82 artisan view:clear
php82 -r "if (function_exists('opcache_reset')) opcache_reset();"
echo DONE
