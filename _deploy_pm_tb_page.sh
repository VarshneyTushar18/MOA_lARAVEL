#!/bin/bash
set -e
APP="/home/sites/41b/b/ba690bc503/MOA_lARAVEL"
PUB="/home/sites/41b/b/ba690bc503/public_html"
UP="/home/sites/41b/b/ba690bc503/moa_deploy_upload"
cp "$UP/HomeController.php" "$APP/app/Http/Controllers/HomeController.php"
cp "$UP/web.php" "$APP/routes/web.php"
cp "$UP/home.blade.php" "$APP/resources/views/pages/home.blade.php"
cp "$UP/pm-tb-mukt-bharat.blade.php" "$APP/resources/views/pages/pm-tb-mukt-bharat.blade.php"
cp "$UP/read-more-text.blade.php" "$APP/resources/views/partials/read-more-text.blade.php"
cp "$UP/frontend.blade.php" "$APP/resources/views/layout/frontend.blade.php"
cp "$UP/main.css" "$APP/public/assets/css/main.css"
cp "$UP/main.css" "$PUB/assets/css/main.css"
cd "$APP"
php82 artisan view:clear
php82 artisan route:clear
echo DONE
