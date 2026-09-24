#!/bin/bash
set -e
cp /home/sites/41b/b/ba690bc503/moa_deploy_upload/best_practices.blade.php /home/sites/41b/b/ba690bc503/MOA_lARAVEL/resources/views/pages/best_practices.blade.php
cp /home/sites/41b/b/ba690bc503/moa_deploy_upload/pdf-card.blade.php /home/sites/41b/b/ba690bc503/MOA_lARAVEL/resources/views/partials/pdf-card.blade.php
cp /home/sites/41b/b/ba690bc503/moa_deploy_upload/PageSectionsController.php /home/sites/41b/b/ba690bc503/MOA_lARAVEL/app/Http/Controllers/PageSectionsController.php
cp /home/sites/41b/b/ba690bc503/moa_deploy_upload/sections_edit.blade.php /home/sites/41b/b/ba690bc503/MOA_lARAVEL/resources/views/pages_console/sections/edit.blade.php
cp /home/sites/41b/b/ba690bc503/moa_deploy_upload/sections_add.blade.php /home/sites/41b/b/ba690bc503/MOA_lARAVEL/resources/views/pages_console/sections/add.blade.php
cp /home/sites/41b/b/ba690bc503/moa_deploy_upload/sections_list.blade.php /home/sites/41b/b/ba690bc503/MOA_lARAVEL/resources/views/pages_console/sections/list.blade.php
cd /home/sites/41b/b/ba690bc503/MOA_lARAVEL
php82 artisan view:clear
php82 artisan view:cache
php82 artisan route:clear
echo DONE
