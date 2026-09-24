#!/bin/bash
set -e
APP="/home/sites/41b/b/ba690bc503/MOA_lARAVEL"
WEB="/home/sites/41b/b/ba690bc503/public_html"
UP="/home/sites/41b/b/ba690bc503/moa_deploy_upload"
mkdir -p "$APP/app/Support" "$APP/app/Exports" "$APP/app/Imports" "$APP/resources/views/pages" "$APP/resources/views/patient_console"
cp "$UP/OpdPatientFields.php" "$APP/app/Support/OpdPatientFields.php"
cp "$UP/PatientsExport.php" "$APP/app/Exports/PatientsExport.php"
cp "$UP/PatientsImport.php" "$APP/app/Imports/PatientsImport.php"
cp "$UP/patient_corner.blade.php" "$APP/resources/views/pages/patient_corner.blade.php"
cp "$UP/patient_console_show.blade.php" "$APP/resources/views/patient_console/show.blade.php"
cp "$UP/main.css" "$APP/public/assets/css/main.css"
cp "$UP/main.css" "$WEB/assets/css/main.css"
cd "$APP"
php82 artisan view:clear
php82 -r "if (function_exists('opcache_reset')) opcache_reset();"
echo DONE
