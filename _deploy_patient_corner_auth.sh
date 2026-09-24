#!/bin/bash
set -e
APP="/home/sites/41b/b/ba690bc503/MOA_lARAVEL"
UP="/home/sites/41b/b/ba690bc503/moa_deploy_upload"
mkdir -p "$APP/app/Models" "$APP/app/Services" "$APP/app/Http/Middleware" "$APP/app/Http/Controllers" "$APP/app/Http/Controllers/Console" "$APP/database/migrations" "$APP/resources/views/pages" "$APP/resources/views/console/patient_corner_access"
cp "$UP/SiteSetting.php" "$APP/app/Models/SiteSetting.php"
cp "$UP/PatientCornerAuthService.php" "$APP/app/Services/PatientCornerAuthService.php"
cp "$UP/EnsurePatientCornerAuthenticated.php" "$APP/app/Http/Middleware/EnsurePatientCornerAuthenticated.php"
cp "$UP/PatientCornerAuthController.php" "$APP/app/Http/Controllers/PatientCornerAuthController.php"
cp "$UP/PatientCornerAccessController.php" "$APP/app/Http/Controllers/Console/PatientCornerAccessController.php"
cp "$UP/Kernel.php" "$APP/app/Http/Kernel.php"
cp "$UP/web.php" "$APP/routes/web.php"
cp "$UP/2026_09_22_160000_create_site_settings_table.php" "$APP/database/migrations/2026_09_22_160000_create_site_settings_table.php"
cp "$UP/patient_corner_login.blade.php" "$APP/resources/views/pages/patient_corner_login.blade.php"
cp "$UP/patient_corner.blade.php" "$APP/resources/views/pages/patient_corner.blade.php"
cp "$UP/patient_corner_access_edit.blade.php" "$APP/resources/views/console/patient_corner_access/edit.blade.php"
cp "$UP/dashboard.blade.php" "$APP/resources/views/console/dashboard.blade.php"
cd "$APP"
php82 artisan migrate --force
php82 artisan view:clear
php82 artisan route:clear
php82 artisan config:clear
php82 -r "if (function_exists('opcache_reset')) opcache_reset();"
echo DONE
