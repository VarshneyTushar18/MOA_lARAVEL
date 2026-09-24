#!/bin/bash
set -e
APP="/home/sites/41b/b/ba690bc503/MOA_lARAVEL"
UP="/home/sites/41b/b/ba690bc503/moa_deploy_upload"
mkdir -p "$APP/app/Http/Controllers" "$APP/app/Services" "$APP/app/Exports" "$APP/resources/views/pages" "$APP/resources/views/console/survey_responses"
cp "$UP/SurveyResponseController.php" "$APP/app/Http/Controllers/SurveyResponseController.php"
cp "$UP/GoogleSurveyFormSyncService.php" "$APP/app/Services/GoogleSurveyFormSyncService.php"
cp "$UP/SurveyResponsesExport.php" "$APP/app/Exports/SurveyResponsesExport.php"
cp "$UP/screening_performa.blade.php" "$APP/resources/views/pages/screening_performa.blade.php"
cp "$UP/survey_responses_show.blade.php" "$APP/resources/views/console/survey_responses/show.blade.php"
cd "$APP"
php82 artisan view:clear
php82 artisan config:clear
php82 -r "if (function_exists('opcache_reset')) opcache_reset();"
echo DONE
