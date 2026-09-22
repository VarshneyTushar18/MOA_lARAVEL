import subprocess
import os

script = r"""#!/bin/bash
set -e
UP="/home/sites/41b/b/ba690bc503/moa_deploy_upload"
APP="/home/sites/41b/b/ba690bc503/MOA_lARAVEL"
HOME_DIR="/home/sites/41b/b/ba690bc503"
DB_HOST="sdb-79.hosting.stackcp.net"
DB_NAME="wordpress-35303833ad48"
DB_USER="wordpress-35303833ad48"
DB_PASS="9a947ecd5f067145"
APP_URL="https://phi-ltbi-aiia.in"

grep -v '^mysqldump:' "$UP/moa_db.sql" > "$UP/moa_db_clean.sql"
mysql -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" < "$UP/moa_db_clean.sql"

cd "$APP"
test -f artisan

cat > .env <<ENV
APP_NAME=MOA
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_URL=$APP_URL
APP_TIMEZONE=Asia/Kolkata
LOG_CHANNEL=stack
LOG_LEVEL=error
DB_CONNECTION=mysql
DB_HOST=$DB_HOST
DB_PORT=3306
DB_DATABASE=$DB_NAME
DB_USERNAME=$DB_USER
DB_PASSWORD=$DB_PASS
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

php82 /usr/local/bin/composer install --no-dev --optimize-autoloader --no-interaction 2>&1 | tail -20
php82 artisan key:generate --force
php82 artisan storage:link 2>/dev/null || true
php82 artisan config:cache
php82 artisan route:cache
php82 artisan view:cache
chmod -R u+rwX storage bootstrap/cache

rm -f "$HOME_DIR/public_html"
ln -sfn "$APP/public" "$HOME_DIR/public_html"

cat > public/.htaccess <<'HTA'
<IfModule mod_rewrite.c>
    <IfModule mod_negotiation.c>
        Options -MultiViews -Indexes
    </IfModule>
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
AddHandler application/x-httpd-php82 .php
HTA

php82 artisan --version
curl -s -o /dev/null -w "home=%{http_code}\n" "$APP_URL/"
curl -s -o /dev/null -w "login=%{http_code}\n" "$APP_URL/console/login"
echo DONE
"""

path = os.path.join(os.path.dirname(__file__), "_finish_deploy.sh")
with open(path, "w", encoding="utf-8", newline="\n") as f:
    f.write(script)

key = os.path.expanduser("~/.ssh/id_ed25519")
host = "phi-ltbi-aiia.in@ssh.gb.stackcp.com"
remote = "/home/sites/41b/b/ba690bc503/moa_deploy_upload/finish_deploy.sh"
subprocess.run(["scp", "-i", key, path, f"{host}:{remote}"], check=True)
r = subprocess.run(["ssh", "-i", key, host, f"chmod +x {remote} && bash {remote}"], capture_output=True, text=True, timeout=1800)
print(r.stdout)
print(r.stderr)
raise SystemExit(r.returncode)
