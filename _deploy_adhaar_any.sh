#!/bin/bash
set -e
APP="/home/sites/41b/b/ba690bc503/MOA_lARAVEL"
UP="/home/sites/41b/b/ba690bc503/moa_deploy_upload"
mkdir -p "$APP/app/Http/Controllers" "$APP/app/Imports" "$APP/resources/views/pages" "$APP/database/migrations"
cp "$UP/PatientController.php" "$APP/app/Http/Controllers/PatientController.php"
cp "$UP/PatientsImport.php" "$APP/app/Imports/PatientsImport.php"
cp "$UP/patient_corner.blade.php" "$APP/resources/views/pages/patient_corner.blade.php"
cp "$UP/2026_09_22_170000_widen_patients_adhaar_no_column.php" "$APP/database/migrations/2026_09_22_170000_widen_patients_adhaar_no_column.php"
cd "$APP"
php82 artisan migrate --force
php82 artisan view:clear
php82 -r "if (function_exists('opcache_reset')) opcache_reset();"
echo DONE
