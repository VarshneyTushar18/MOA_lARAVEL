#!/bin/bash
set -e
APP="/home/sites/41b/b/ba690bc503/MOA_lARAVEL"
UP="/home/sites/41b/b/ba690bc503/moa_deploy_upload"

mkdir -p "$APP/app/Services"
mkdir -p "$APP/app/Http/Controllers/Console"
mkdir -p "$APP/resources/views/partials"
mkdir -p "$APP/resources/views/footer_console/partials"

cp "$UP/FooterContentService.php" "$APP/app/Services/FooterContentService.php"
cp "$UP/FooterController.php" "$APP/app/Http/Controllers/Console/FooterController.php"
cp "$UP/AppServiceProvider.php" "$APP/app/Providers/AppServiceProvider.php"
cp "$UP/PagesController.php" "$APP/app/Http/Controllers/PagesController.php"
cp "$UP/web.php" "$APP/routes/web.php"
cp "$UP/site-footer.blade.php" "$APP/resources/views/partials/site-footer.blade.php"
cp "$UP/footer_edit.blade.php" "$APP/resources/views/footer_console/edit.blade.php"
cp "$UP/link-row.blade.php" "$APP/resources/views/footer_console/partials/link-row.blade.php"
cp "$UP/frontend.blade.php" "$APP/resources/views/layout/frontend.blade.php"
cp "$UP/console.blade.php" "$APP/resources/views/layout/console.blade.php"
cp "$UP/dashboard.blade.php" "$APP/resources/views/console/dashboard.blade.php"

cd "$APP"
php82 artisan view:clear
php82 artisan route:clear
php82 artisan config:clear
php82 artisan config:cache
echo DONE
