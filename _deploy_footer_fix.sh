#!/bin/bash
set -e
HOME_DIR="/home/sites/41b/b/ba690bc503"
APP="$HOME_DIR/MOA_lARAVEL"
UP="$HOME_DIR/moa_deploy_upload"
cp "$UP/frontend.blade.php" "$APP/resources/views/layout/frontend.blade.php"
cd "$APP"
php82 artisan view:clear
echo DONE
grep -n 'social-media' "$APP/resources/views/layout/frontend.blade.php" || echo social-media-removed
