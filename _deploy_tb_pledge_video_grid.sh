#!/bin/bash
set -e
cp /home/sites/41b/b/ba690bc503/moa_deploy_upload/acsm-video-gallery.blade.php /home/sites/41b/b/ba690bc503/MOA_lARAVEL/resources/views/partials/acsm-video-gallery.blade.php
cp /home/sites/41b/b/ba690bc503/moa_deploy_upload/acsm-video-player.blade.php /home/sites/41b/b/ba690bc503/MOA_lARAVEL/resources/views/partials/acsm-video-player.blade.php
cp /home/sites/41b/b/ba690bc503/moa_deploy_upload/frontend.blade.php /home/sites/41b/b/ba690bc503/MOA_lARAVEL/resources/views/layout/frontend.blade.php
cp /home/sites/41b/b/ba690bc503/moa_deploy_upload/main.css /home/sites/41b/b/ba690bc503/MOA_lARAVEL/public/assets/css/main.css
cp /home/sites/41b/b/ba690bc503/moa_deploy_upload/main.css /home/sites/41b/b/ba690bc503/public_html/assets/css/main.css
cd /home/sites/41b/b/ba690bc503/MOA_lARAVEL
php82 artisan view:clear
php82 artisan view:cache
echo DONE
