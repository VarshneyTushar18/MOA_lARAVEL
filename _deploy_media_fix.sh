#!/bin/bash
set -e
cp /home/sites/41b/b/ba690bc503/moa_deploy_upload/PageSectionsController.php /home/sites/41b/b/ba690bc503/MOA_lARAVEL/app/Http/Controllers/PageSectionsController.php
cp /home/sites/41b/b/ba690bc503/moa_deploy_upload/PageSection.php /home/sites/41b/b/ba690bc503/MOA_lARAVEL/app/Models/PageSection.php
cp /home/sites/41b/b/ba690bc503/moa_deploy_upload/PageSectionMedia.php /home/sites/41b/b/ba690bc503/MOA_lARAVEL/app/Models/PageSectionMedia.php
cp /home/sites/41b/b/ba690bc503/moa_deploy_upload/migration_sort_order_media.php /home/sites/41b/b/ba690bc503/MOA_lARAVEL/database/migrations/2026_09_24_100000_add_sort_order_to_page_section_media_table.php
cp /home/sites/41b/b/ba690bc503/moa_deploy_upload/sections_edit.blade.php /home/sites/41b/b/ba690bc503/MOA_lARAVEL/resources/views/pages_console/sections/edit.blade.php
cp /home/sites/41b/b/ba690bc503/moa_deploy_upload/sections_list.blade.php /home/sites/41b/b/ba690bc503/MOA_lARAVEL/resources/views/pages_console/sections/list.blade.php
cp /home/sites/41b/b/ba690bc503/moa_deploy_upload/acsm_iec.blade.php /home/sites/41b/b/ba690bc503/MOA_lARAVEL/resources/views/pages/acsm_iec.blade.php
cp /home/sites/41b/b/ba690bc503/moa_deploy_upload/acsm-section.blade.php /home/sites/41b/b/ba690bc503/MOA_lARAVEL/resources/views/partials/acsm-section.blade.php
cp /home/sites/41b/b/ba690bc503/moa_deploy_upload/acsm-video-gallery.blade.php /home/sites/41b/b/ba690bc503/MOA_lARAVEL/resources/views/partials/acsm-video-gallery.blade.php
cd /home/sites/41b/b/ba690bc503/MOA_lARAVEL
php82 artisan migrate --force
php82 artisan view:clear
php82 artisan view:cache
php82 -r "if (function_exists('opcache_reset')) opcache_reset();"
echo DONE
