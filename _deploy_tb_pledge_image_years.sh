#!/bin/bash
set -e
cp /home/sites/41b/b/ba690bc503/moa_deploy_upload/2026_09_24_120000_add_sort_order_to_page_section_images_table.php /home/sites/41b/b/ba690bc503/MOA_lARAVEL/database/migrations/
cp /home/sites/41b/b/ba690bc503/moa_deploy_upload/MediaYearResolver.php /home/sites/41b/b/ba690bc503/MOA_lARAVEL/app/Support/MediaYearResolver.php
cp /home/sites/41b/b/ba690bc503/moa_deploy_upload/PageSectionImage.php /home/sites/41b/b/ba690bc503/MOA_lARAVEL/app/Models/PageSectionImage.php
cp /home/sites/41b/b/ba690bc503/moa_deploy_upload/acsm-tb-pledge-year-tabs.blade.php /home/sites/41b/b/ba690bc503/MOA_lARAVEL/resources/views/partials/acsm-tb-pledge-year-tabs.blade.php
cp /home/sites/41b/b/ba690bc503/moa_deploy_upload/frontend_tb_images.php /home/sites/41b/b/ba690bc503/MOA_lARAVEL/resources/views/layout/frontend.blade.php
cp /home/sites/41b/b/ba690bc503/moa_deploy_upload/assign_tb_pledge_image_years.php /home/sites/41b/b/ba690bc503/MOA_lARAVEL/_assign_tb_pledge_image_years.php
cp /home/sites/41b/b/ba690bc503/moa_deploy_upload/tb_pledge_image_manifest.json /home/sites/41b/b/ba690bc503/MOA_lARAVEL/_tb_pledge_image_manifest.json
cp /home/sites/41b/b/ba690bc503/moa_deploy_upload/check_tb_pledge_year_counts.php /home/sites/41b/b/ba690bc503/MOA_lARAVEL/_check_tb_pledge_year_counts.php
cd /home/sites/41b/b/ba690bc503/MOA_lARAVEL
php82 artisan migrate --force
php82 _assign_tb_pledge_image_years.php _tb_pledge_image_manifest.json
echo "--- after assign ---"
php82 _check_tb_pledge_year_counts.php
php82 artisan view:clear
php82 artisan view:cache
echo DONE
