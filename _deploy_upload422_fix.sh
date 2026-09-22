#!/bin/bash
set -e
APP="/home/sites/41b/b/ba690bc503/MOA_lARAVEL"
PUB="/home/sites/41b/b/ba690bc503/public_html"
UP="/home/sites/41b/b/ba690bc503/moa_deploy_upload"
cp "$UP/PageSectionsController.php" "$APP/app/Http/Controllers/PageSectionsController.php"
cp "$UP/sections_edit.blade.php" "$APP/resources/views/pages_console/sections/edit.blade.php"
cp "$UP/sections_add.blade.php" "$APP/resources/views/pages_console/sections/add.blade.php"
cp "$UP/console-upload-progress.js" "$APP/public/assets/js/console-upload-progress.js"
cp "$UP/console-upload-progress.js" "$PUB/assets/js/console-upload-progress.js"
cp "$UP/console.blade.php" "$APP/resources/views/layout/console.blade.php"
cd "$APP"
php82 artisan view:clear
echo DONE
