#!/bin/bash
set -e
cp /home/sites/41b/b/ba690bc503/moa_deploy_upload/.user.ini /home/sites/41b/b/ba690bc503/MOA_lARAVEL/public/.user.ini
cp /home/sites/41b/b/ba690bc503/moa_deploy_upload/.user.ini /home/sites/41b/b/ba690bc503/public_html/.user.ini
cp /home/sites/41b/b/ba690bc503/moa_deploy_upload/PageSectionsController.php /home/sites/41b/b/ba690bc503/MOA_lARAVEL/app/Http/Controllers/PageSectionsController.php
cp /home/sites/41b/b/ba690bc503/moa_deploy_upload/ConsoleAccountController.php /home/sites/41b/b/ba690bc503/MOA_lARAVEL/app/Http/Controllers/ConsoleAccountController.php
cp /home/sites/41b/b/ba690bc503/moa_deploy_upload/AppServiceProvider.php /home/sites/41b/b/ba690bc503/MOA_lARAVEL/app/Providers/AppServiceProvider.php
cp /home/sites/41b/b/ba690bc503/moa_deploy_upload/web.php /home/sites/41b/b/ba690bc503/MOA_lARAVEL/routes/web.php
cp /home/sites/41b/b/ba690bc503/moa_deploy_upload/console-upload-progress.js /home/sites/41b/b/ba690bc503/MOA_lARAVEL/public/assets/js/console-upload-progress.js
cp /home/sites/41b/b/ba690bc503/moa_deploy_upload/console-upload-progress.js /home/sites/41b/b/ba690bc503/public_html/assets/js/console-upload-progress.js
cp /home/sites/41b/b/ba690bc503/moa_deploy_upload/console.blade.php /home/sites/41b/b/ba690bc503/MOA_lARAVEL/resources/views/layout/console.blade.php
cp /home/sites/41b/b/ba690bc503/moa_deploy_upload/console_login.blade.php /home/sites/41b/b/ba690bc503/MOA_lARAVEL/resources/views/console/login.blade.php
mkdir -p /home/sites/41b/b/ba690bc503/MOA_lARAVEL/resources/views/console/account
cp /home/sites/41b/b/ba690bc503/moa_deploy_upload/console_account_password.blade.php /home/sites/41b/b/ba690bc503/MOA_lARAVEL/resources/views/console/account/password.blade.php
cp /home/sites/41b/b/ba690bc503/moa_deploy_upload/console_forgot_password.blade.php /home/sites/41b/b/ba690bc503/MOA_lARAVEL/resources/views/console/forgot-password.blade.php
cp /home/sites/41b/b/ba690bc503/moa_deploy_upload/console_reset_password.blade.php /home/sites/41b/b/ba690bc503/MOA_lARAVEL/resources/views/console/reset-password.blade.php
cp /home/sites/41b/b/ba690bc503/moa_deploy_upload/merge_patient_medicine_sections.php /home/sites/41b/b/ba690bc503/MOA_lARAVEL/_merge_patient_medicine_sections.php
cd /home/sites/41b/b/ba690bc503/MOA_lARAVEL
sed -i 's/^UPLOAD_MAX_VIDEO_MB=.*/UPLOAD_MAX_VIDEO_MB=0/' .env || true
grep -q '^UPLOAD_MAX_VIDEO_MB=' .env || echo 'UPLOAD_MAX_VIDEO_MB=0' >> .env
php82 _merge_patient_medicine_sections.php
php82 artisan config:clear
php82 artisan route:clear
php82 artisan view:clear
php82 artisan view:cache
if php82 -r "if (function_exists('opcache_reset')) opcache_reset();" 2>/dev/null; then echo opcache_cleared; fi
echo DONE
