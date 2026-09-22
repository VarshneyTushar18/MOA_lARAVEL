#!/bin/bash
set -e
APP="/home/sites/41b/b/ba690bc503/MOA_lARAVEL"
UP="/home/sites/41b/b/ba690bc503/moa_deploy_upload"
TEST="$UP/test_3gb_upload.mp4"
BASE="https://phi-ltbi-aiia-in.stackstaging.com"

cd "$APP"
rm -f "$TEST"

echo "=== 1) Create 3 GB sparse MP4 test file ==="
php82 -r "
\$path = '$TEST';
\$f = fopen(\$path, 'wb');
// minimal ISO BMFF ftyp header so Laravel accepts video/mp4
fwrite(\$f, hex2bin('000000186674797069736F6D00000000'));
fseek(\$f, 3 * 1024 * 1024 * 1024 - 1);
fwrite(\$f, \"\\0\");
fclose(\$f);
echo 'bytes=' . filesize(\$path) . PHP_EOL;
"

echo "=== 2) Limits ==="
php82 -r "
require 'vendor/autoload.php';
\$app=require 'bootstrap/app.php';
\$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
echo 'app_max_mb=' . config('upload_compression.max_video_mb') . PHP_EOL;
echo 'php_upload=' . ini_get('upload_max_filesize') . PHP_EOL;
echo 'php_post=' . ini_get('post_max_size') . PHP_EOL;
"

echo "=== 3) Laravel validation ==="
php82 -r "
require 'vendor/autoload.php';
\$app=require 'bootstrap/app.php';
\$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Validator;
\$file = new UploadedFile('$TEST', 'test_3gb.mp4', 'video/mp4', null, true);
\$maxKb = (int) config('upload_compression.max_video_mb') * 1024;
\$v = Validator::make(['video'=>\$file], ['video'=>\"file|mimes:mp4,mov,avi|max:\$maxKb\"]);
if (\$v->fails()) { echo 'VALIDATION_FAIL: ' . \$v->errors()->first() . PHP_EOL; exit(1); }
echo 'VALIDATION_OK size=' . \$file->getSize() . PHP_EOL;
"

echo "=== 4) Store + delete (app pipeline) ==="
STORED=$(php82 -r "
require 'vendor/autoload.php';
\$app=require 'bootstrap/app.php';
\$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
use Illuminate\Http\UploadedFile;
use App\Services\CompressedUploadStorage;
\$file = new UploadedFile('$TEST', 'test_3gb.mp4', 'video/mp4', null, true);
echo CompressedUploadStorage::storeVideo(\$file, 'page_sections/videos/test_upload', 'public');
")
echo "stored_path=$STORED"
php82 -r "
require 'vendor/autoload.php';
\$app=require 'bootstrap/app.php';
\$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
\$p='$STORED';
\$full=storage_path('app/public/'.\$p);
echo 'stored_bytes='.(is_file(\$full)?filesize(\$full):0).PHP_EOL;
Illuminate\Support\Facades\Storage::disk('public')->delete(\$p);
echo 'storage_deleted'.PHP_EOL;
"

echo "=== 5) HTTP browser upload test ==="
COOKIE="$UP/cookies.txt"
rm -f "$COOKIE"
PAGE_ID=$(php82 -r "require 'vendor/autoload.php';\$a=require 'bootstrap/app.php';\$a->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();echo App\Models\Page::where('slug','acsm_iec')->value('id');")
php82 artisan tinker --execute="App\Models\User::where('email','admin@example.com')->update(['password'=>bcrypt('TestUpload3gb!')]);" >/dev/null 2>&1 || true
LOGIN_HTML=$(curl -s -c "$COOKIE" "$BASE/console/login")
TOKEN=$(echo "$LOGIN_HTML" | sed -n 's/.*name="_token" value="\([^"]*\)".*/\1/p' | head -1)
curl -s -c "$COOKIE" -b "$COOKIE" -L -X POST "$BASE/console/login" -d "_token=$TOKEN" -d "email=admin@example.com" -d "password=TestUpload3gb!" -o /dev/null -w "login=%{http_code}\n"
EDIT_HTML=$(curl -s -c "$COOKIE" -b "$COOKIE" "$BASE/console/pages/sections/${PAGE_ID}/edit/52")
TOKEN2=$(echo "$EDIT_HTML" | sed -n 's/.*name="_token" value="\([^"]*\)".*/\1/p' | head -1)
HTTP_CODE=$(curl -s -c "$COOKIE" -b "$COOKIE" -L --max-time 120 -X POST "$BASE/console/pages/sections/${PAGE_ID}/edit/52" \
  -F "_token=$TOKEN2" -F "section_key=launch_video" -F "videos[]=@${TEST};type=video/mp4" \
  -o "$UP/http_response.html" -w "%{http_code}")
echo "http_upload=$HTTP_CODE"
head -c 500 "$UP/http_response.html" 2>/dev/null | tr '\n' ' ' | head -c 300; echo

echo "=== 6) Cleanup ==="
rm -f "$TEST" "$COOKIE" "$UP/http_response.html"
find "$APP/storage/app/public/page_sections/videos" -name '*test_upload*' -delete 2>/dev/null || true
php82 artisan tinker --execute="DB::table('page_section_media')->where('file_path','like','%test_upload%')->delete();" >/dev/null 2>&1 || true
echo TEST_DONE
