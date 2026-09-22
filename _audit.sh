BASE="https://phi-ltbi-aiia-in.stackstaging.com"
echo MODULE|ROUTE|METHOD|HTTP|STATUS
code=$(curl -s -o /dev/null -w "%{http_code}" -X GET "$BASE/"); [ "$code" = "200" ] && st=PASS || st=FAIL; echo "Public pages|/|GET|$code|$st"
code=$(curl -s -o /dev/null -w "%{http_code}" -X GET "$BASE/about"); [ "$code" = "200" ] && st=PASS || st=FAIL; echo "Public pages|/about|GET|$code|$st"
code=$(curl -s -o /dev/null -w "%{http_code}" -X GET "$BASE/factsheet"); [ "$code" = "200" ] && st=PASS || st=FAIL; echo "Public pages|/factsheet|GET|$code|$st"
code=$(curl -s -o /dev/null -w "%{http_code}" -X GET "$BASE/acsm_iec"); [ "$code" = "200" ] && st=PASS || st=FAIL; echo "Public pages|/acsm_iec|GET|$code|$st"
code=$(curl -s -o /dev/null -w "%{http_code}" -X GET "$BASE/best_practices"); [ "$code" = "200" ] && st=PASS || st=FAIL; echo "Public pages|/best_practices|GET|$code|$st"
code=$(curl -s -o /dev/null -w "%{http_code}" -X GET "$BASE/patient_corner"); [ "$code" = "200" ] && st=PASS || st=FAIL; echo "Public pages|/patient_corner|GET|$code|$st"
code=$(curl -s -o /dev/null -w "%{http_code}" -X GET "$BASE/performance_report"); [ "$code" = "200" ] && st=PASS || st=FAIL; echo "Public pages|/performance_report|GET|$code|$st"
code=$(curl -s -o /dev/null -w "%{http_code}" -X GET "$BASE/contact"); [ "$code" = "200" ] && st=PASS || st=FAIL; echo "Public pages|/contact|GET|$code|$st"
code=$(curl -s -o /dev/null -w "%{http_code}" -X GET "$BASE/screening-performa"); [ "$code" = "200" ] && st=PASS || st=FAIL; echo "Survey form|/screening-performa|GET|$code|$st"
code=$(curl -s -o /dev/null -w "%{http_code}" -X POST "$BASE/survey-submit"); ok=0; for e in 422 302 419 405; do [ "$code" = "$e" ] && ok=1; done; [ "$ok" = "1" ] && st=PASS || st=FAIL; echo "Survey submit|/survey-submit|POST|$code|$st"
code=$(curl -s -o /dev/null -w "%{http_code}" -X GET "$BASE/contact"); [ "$code" = "200" ] && st=PASS || st=FAIL; echo "Contact form page|/contact|GET|$code|$st"
code=$(curl -s -o /dev/null -w "%{http_code}" -X POST "$BASE/contact-submit"); ok=0; for e in 422 302 419; do [ "$code" = "$e" ] && ok=1; done; [ "$ok" = "1" ] && st=PASS || st=FAIL; echo "Contact submit|/contact-submit|POST|$code|$st"
code=$(curl -s -o /dev/null -w "%{http_code}" -X POST "$BASE/patients/store"); ok=0; for e in 422 302 419; do [ "$code" = "$e" ] && ok=1; done; [ "$ok" = "1" ] && st=PASS || st=FAIL; echo "Patient form submit|/patients/store|POST|$code|$st"
code=$(curl -s -o /dev/null -w "%{http_code}" -X POST "$BASE/upload-opd"); ok=0; for e in 422 302 419; do [ "$code" = "$e" ] && ok=1; done; [ "$ok" = "1" ] && st=PASS || st=FAIL; echo "OPD upload|/upload-opd|POST|$code|$st"
code=$(curl -s -o /dev/null -w "%{http_code}" -X POST "$BASE/cure/store"); ok=0; for e in 422 302 419; do [ "$code" = "$e" ] && ok=1; done; [ "$ok" = "1" ] && st=PASS || st=FAIL; echo "Cure upload|/cure/store|POST|$code|$st"
code=$(curl -s -o /dev/null -w "%{http_code}" -X POST "$BASE/research/store"); ok=0; for e in 422 302 419; do [ "$code" = "$e" ] && ok=1; done; [ "$ok" = "1" ] && st=PASS || st=FAIL; echo "Research upload|/research/store|POST|$code|$st"
code=$(curl -s -o /dev/null -w "%{http_code}" -X POST "$BASE/idcard/store"); ok=0; for e in 422 302 419; do [ "$code" = "$e" ] && ok=1; done; [ "$ok" = "1" ] && st=PASS || st=FAIL; echo "ID card upload|/idcard/store|POST|$code|$st"
code=$(curl -s -o /dev/null -w "%{http_code}" -X GET "$BASE/console/login"); [ "$code" = "200" ] && st=PASS || st=FAIL; echo "Admin login|/console/login|GET|$code|$st"
code=$(curl -s -o /dev/null -w "%{http_code}" -X GET "$BASE/console/dashboard"); ok=0; for e in 302 401; do [ "$code" = "$e" ] && ok=1; done; [ "$ok" = "1" ] && st=PASS || st=FAIL; echo "Admin dashboard|/console/dashboard|GET|$code|$st"
code=$(curl -s -o /dev/null -w "%{http_code}" -X GET "$BASE/console/patients/list"); ok=0; for e in 302 401; do [ "$code" = "$e" ] && ok=1; done; [ "$ok" = "1" ] && st=PASS || st=FAIL; echo "Admin patients list|/console/patients/list|GET|$code|$st"
code=$(curl -s -o /dev/null -w "%{http_code}" -X GET "$BASE/console/contacts/list"); ok=0; for e in 302 401; do [ "$code" = "$e" ] && ok=1; done; [ "$ok" = "1" ] && st=PASS || st=FAIL; echo "Admin contacts list|/console/contacts/list|GET|$code|$st"
code=$(curl -s -o /dev/null -w "%{http_code}" -X GET "$BASE/console/cure-patients/list"); ok=0; for e in 302 401; do [ "$code" = "$e" ] && ok=1; done; [ "$ok" = "1" ] && st=PASS || st=FAIL; echo "Admin cure list|/console/cure-patients/list|GET|$code|$st"
code=$(curl -s -o /dev/null -w "%{http_code}" -X GET "$BASE/console/research-patients/list"); ok=0; for e in 302 401; do [ "$code" = "$e" ] && ok=1; done; [ "$ok" = "1" ] && st=PASS || st=FAIL; echo "Admin research list|/console/research-patients/list|GET|$code|$st"
code=$(curl -s -o /dev/null -w "%{http_code}" -X GET "$BASE/console/id-cards/list"); ok=0; for e in 302 401; do [ "$code" = "$e" ] && ok=1; done; [ "$ok" = "1" ] && st=PASS || st=FAIL; echo "Admin idcard list|/console/id-cards/list|GET|$code|$st"
code=$(curl -s -o /dev/null -w "%{http_code}" -X GET "$BASE/console/survey-responses"); ok=0; for e in 302 401; do [ "$code" = "$e" ] && ok=1; done; [ "$ok" = "1" ] && st=PASS || st=FAIL; echo "Admin surveys|/console/survey-responses|GET|$code|$st"
code=$(curl -s -o /dev/null -w "%{http_code}" -X GET "$BASE/console/pages/list"); ok=0; for e in 302 401; do [ "$code" = "$e" ] && ok=1; done; [ "$ok" = "1" ] && st=PASS || st=FAIL; echo "Admin pages|/console/pages/list|GET|$code|$st"
code=$(curl -s -o /dev/null -w "%{http_code}" -X GET "$BASE/screening-performa"); [ "$code" = "200" ] && st=PASS || st=FAIL; echo "Survey form link on home|/screening-performa|GET|$code|$st"

cd /home/sites/41b/b/ba690bc503/MOA_lARAVEL
echo "---DB_COUNTS---"
php82 -r "
require 'vendor/autoload.php';
\$a=require 'bootstrap/app.php';
\$a->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
echo 'patients='.App\Models\Patient::count().PHP_EOL;
echo 'contacts='.App\Models\Contact::count().PHP_EOL;
echo 'surveys='.App\Models\SurveyResponse::count().PHP_EOL;
echo 'cure='.App\Models\CurePatient::count().PHP_EOL;
echo 'research='.App\Models\ResearchPatient::count().PHP_EOL;
echo 'idcards='.App\Models\IdCard::count().PHP_EOL;
echo 'pages='.App\Models\Page::count().PHP_EOL;
echo 'sections='.App\Models\PageSection::count().PHP_EOL;
"
echo "---LIMITS---"
grep -E '^(APP_URL|UPLOAD_MAX_VIDEO_MB)=' .env
cat ../public_html/.user.ini 2>/dev/null | head -4
php82 -r 'echo "cli_upload=".ini_get("upload_max_filesize")." cli_post=".ini_get("post_max_size").PHP_EOL;'
php82 -r "require 'vendor/autoload.php';\$a=require 'bootstrap/app.php';\$a->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap(); echo 'app_video_mb='.config('upload_compression.max_video_mb').PHP_EOL;"
