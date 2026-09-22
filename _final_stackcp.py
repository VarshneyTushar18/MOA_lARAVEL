import subprocess
import os

script = r"""#!/bin/bash
set -e
HOME_DIR="/home/sites/41b/b/ba690bc503"
APP="$HOME_DIR/MOA_lARAVEL"
PUB="$HOME_DIR/public_html"

cat > "$HOME_DIR/.htaccess" <<'HTA'
#+PHPVersion
#=php82
AddHandler x-httpd-php82 .php
#-PHPVersion
HTA

cat > "$PUB/.htaccess" <<'HTA'
#+PHPVersion
#=php82
AddHandler x-httpd-php82 .php
#-PHPVersion
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteCond %{HTTP:Authorization} .
    RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_URI} (.+)/$
    RewriteRule ^ %1 [L,R=301]
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteRule ^ index.php [L]
</IfModule>
HTA

cat > "$PUB/index.php" <<'PHP'
<?php
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Http\Request;
define('LARAVEL_START', microtime(true));
if (file_exists($maintenance = __DIR__.'/../MOA_lARAVEL/storage/framework/maintenance.php')) {
    require $maintenance;
}
require __DIR__.'/../MOA_lARAVEL/vendor/autoload.php';
$app = require_once __DIR__.'/../MOA_lARAVEL/bootstrap/app.php';
$kernel = $app->make(Kernel::class);
$response = $kernel->handle($request = Request::capture())->send();
$kernel->terminate($request, $response);
PHP

rm -f "$PUB/storage"
ln -sfn "$APP/storage/app/public" "$PUB/storage"

cat > "$APP/.env" <<ENV
APP_NAME=MOA
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_URL=https://phi-ltbi-aiia.in
APP_TIMEZONE=Asia/Kolkata
LOG_CHANNEL=stack
LOG_LEVEL=error
DB_CONNECTION=mysql
DB_HOST=sdb-79.hosting.stackcp.net
DB_PORT=3306
DB_DATABASE=wordpress-35303833ad48
DB_USERNAME=wordpress-35303833ad48
DB_PASSWORD=9a947ecd5f067145
BROADCAST_DRIVER=log
CACHE_DRIVER=file
FILESYSTEM_DISK=local
QUEUE_CONNECTION=sync
SESSION_DRIVER=file
SESSION_LIFETIME=120
UPLOAD_IMAGE_COMPRESSION=true
UPLOAD_VIDEO_COMPRESSION=false
UPLOAD_MAX_VIDEO_MB=0
ENV

cd "$APP"
php82 artisan key:generate --force
php82 artisan config:cache
php82 artisan route:cache
php82 artisan view:cache
chmod -R u+rwX storage bootstrap/cache
php82 artisan --version
mysql -h sdb-79.hosting.stackcp.net -u wordpress-35303833ad48 -p'9a947ecd5f067145' wordpress-35303833ad48 -e 'SELECT COUNT(*) AS patients FROM patients; SELECT COUNT(*) AS pages FROM pages;' 2>/dev/null
echo FINAL_OK
"""

path = os.path.join(os.path.dirname(__file__), "_final_stackcp.sh")
with open(path, "w", encoding="utf-8", newline="\n") as f:
    f.write(script)
key = os.path.expanduser("~/.ssh/id_ed25519")
host = "phi-ltbi-aiia.in@ssh.gb.stackcp.com"
remote = "/home/sites/41b/b/ba690bc503/moa_deploy_upload/final_stackcp.sh"
subprocess.run(["scp", "-i", key, path, f"{host}:{remote}"], check=True)
r = subprocess.run(["ssh", "-i", key, host, f"bash {remote}"], capture_output=True, text=True, timeout=300)
print(r.stdout)
if r.stderr:
    print(r.stderr)
