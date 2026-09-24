#!/bin/bash
set -e
APP="/home/sites/41b/b/ba690bc503/MOA_lARAVEL"
PUB="/home/sites/41b/b/ba690bc503/public_html"
UP="/home/sites/41b/b/ba690bc503/moa_deploy_upload"
mkdir -p "$APP/app/Support" "$APP/resources/views/partials" "$APP/resources/views/pages"
cp "$UP/MediaGalleryRules.php" "$APP/app/Support/MediaGalleryRules.php"
cp "$UP/acsm-image-gallery.blade.php" "$APP/resources/views/partials/acsm-image-gallery.blade.php"
cp "$UP/acsm-video-gallery.blade.php" "$APP/resources/views/partials/acsm-video-gallery.blade.php"
cp "$UP/acsm-section.blade.php" "$APP/resources/views/partials/acsm-section.blade.php"
cp "$UP/acsm-gallery-scripts.blade.php" "$APP/resources/views/partials/acsm-gallery-scripts.blade.php"
cp "$UP/acsm_iec.blade.php" "$APP/resources/views/pages/acsm_iec.blade.php"
cp "$UP/best_practices.blade.php" "$APP/resources/views/pages/best_practices.blade.php"
cp "$UP/performance_report.blade.php" "$APP/resources/views/pages/performance_report.blade.php"
cp "$UP/factsheet.blade.php" "$APP/resources/views/pages/factsheet.blade.php"
cp "$UP/frontend.blade.php" "$APP/resources/views/layout/frontend.blade.php"
cp "$UP/main.css" "$APP/public/assets/css/main.css"
cp "$UP/main.css" "$PUB/assets/css/main.css"
cd "$APP"
php82 artisan view:clear
php82 artisan view:cache
echo DONE
