#!/bin/bash
set -e
cp /home/sites/41b/b/ba690bc503/moa_deploy_upload/factsheet.blade.php /home/sites/41b/b/ba690bc503/MOA_lARAVEL/resources/views/pages/factsheet.blade.php
cp /home/sites/41b/b/ba690bc503/moa_deploy_upload/main.css /home/sites/41b/b/ba690bc503/MOA_lARAVEL/public/assets/css/main.css
cp /home/sites/41b/b/ba690bc503/moa_deploy_upload/main.css /home/sites/41b/b/ba690bc503/public_html/assets/css/main.css
cp /home/sites/41b/b/ba690bc503/moa_deploy_upload/frontend.blade.php /home/sites/41b/b/ba690bc503/MOA_lARAVEL/resources/views/layout/frontend.blade.php
cd /home/sites/41b/b/ba690bc503/MOA_lARAVEL
php82 artisan view:clear
php82 artisan view:cache
echo DONE
