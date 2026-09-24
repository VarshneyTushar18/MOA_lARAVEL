#!/bin/bash
set -e
APP="/home/sites/41b/b/ba690bc503/MOA_lARAVEL"
WEB="/home/sites/41b/b/ba690bc503/public_html"
UP="/home/sites/41b/b/ba690bc503/moa_deploy_upload"
mkdir -p "$APP/app/Http/Controllers/Concerns" "$APP/app/Http/Controllers/Console" \
  "$APP/app/Http/Controllers" "$APP/resources/views/partials" "$APP/resources/views/layout" \
  "$APP/resources/views/patient_console" "$APP/resources/views/contacts_console" \
  "$APP/resources/views/cure_console" "$APP/resources/views/research_console" \
  "$APP/resources/views/idcard_console" "$APP/resources/views/console/survey_responses" \
  "$APP/public/assets/js" "$WEB/assets/js"
cp "$UP/FiltersConsoleDateRange.php" "$APP/app/Http/Controllers/Concerns/FiltersConsoleDateRange.php"
cp "$UP/HandlesBulkSelection.php" "$APP/app/Http/Controllers/Concerns/HandlesBulkSelection.php"
cp "$UP/PatientController.php" "$APP/app/Http/Controllers/PatientController.php"
cp "$UP/ContactController.php" "$APP/app/Http/Controllers/ContactController.php"
cp "$UP/CureController.php" "$APP/app/Http/Controllers/CureController.php"
cp "$UP/ResearchPatientController.php" "$APP/app/Http/Controllers/ResearchPatientController.php"
cp "$UP/IdCardController.php" "$APP/app/Http/Controllers/IdCardController.php"
cp "$UP/ConsoleSurveyResponseController.php" "$APP/app/Http/Controllers/Console/SurveyResponseController.php"
cp "$UP/console_date_filter.blade.php" "$APP/resources/views/partials/console_date_filter.blade.php"
cp "$UP/console_bulk_toolbar.blade.php" "$APP/resources/views/partials/console_bulk_toolbar.blade.php"
cp "$UP/console_layout.blade.php" "$APP/resources/views/layout/console.blade.php"
cp "$UP/patient_list.blade.php" "$APP/resources/views/patient_console/list.blade.php"
cp "$UP/contacts_list.blade.php" "$APP/resources/views/contacts_console/list.blade.php"
cp "$UP/cure_list.blade.php" "$APP/resources/views/cure_console/list.blade.php"
cp "$UP/research_list.blade.php" "$APP/resources/views/research_console/list.blade.php"
cp "$UP/idcard_list.blade.php" "$APP/resources/views/idcard_console/list.blade.php"
cp "$UP/survey_index.blade.php" "$APP/resources/views/console/survey_responses/index.blade.php"
cp "$UP/console-bulk-selection.js" "$APP/public/assets/js/console-bulk-selection.js"
cp "$UP/console-bulk-selection.js" "$WEB/assets/js/console-bulk-selection.js"
cd "$APP"
php82 artisan view:clear
php82 -r "if (function_exists('opcache_reset')) opcache_reset();"
echo DONE
