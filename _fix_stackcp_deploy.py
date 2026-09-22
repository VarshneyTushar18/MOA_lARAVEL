#!/usr/bin/env python3
import os
import subprocess
import sys

SSH_KEY = os.path.join(os.path.expanduser("~"), ".ssh", "id_ed25519")
SSH_HOST = "phi-ltbi-aiia.in@ssh.gb.stackcp.com"
REMOTE_HOME = "/home/sites/41b/b/ba690bc503"
REMOTE_APP = f"{REMOTE_HOME}/MOA_lARAVEL"
REMOTE_UPLOAD = f"{REMOTE_HOME}/moa_deploy_upload"
DB_HOST = "sdb-79.hosting.stackcp.net"
DB_NAME = "wordpress-35303833ad48"
DB_USER = "wordpress-35303833ad48"
DB_PASS = "9a947ecd5f067145"
APP_URL = "https://phi-ltbi-aiia.in"

deploy_script = f"""#!/bin/bash
set -e
REMOTE_HOME="{REMOTE_HOME}"
REMOTE_APP="{REMOTE_APP}"
REMOTE_UPLOAD="{REMOTE_UPLOAD}"
DB_HOST="{DB_HOST}"
DB_NAME="{DB_NAME}"
DB_USER="{DB_USER}"
DB_PASS="{DB_PASS}"
APP_URL="{APP_URL}"

cd "$REMOTE_HOME"

echo "Extracting MOA project..."
rm -rf "$REMOTE_APP"
mkdir -p "$REMOTE_APP"
tar -xzf "$REMOTE_UPLOAD/moa_project.tar.gz" -C "$REMOTE_HOME"
test -f "$REMOTE_APP/artisan" || {{ echo "artisan missing after extract"; exit 1; }}

echo "Linking public_html..."
rm -f public_html
ln -sfn "$REMOTE_APP/public" public_html

echo "Importing database..."
mysql -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" < "$REMOTE_UPLOAD/moa_db.sql"

echo "Writing .env..."
cat > "$REMOTE_APP/.env" <<ENV
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
UPLOAD_VIDEO_COMPRESS_ASYNC=false
UPLOAD_MAX_VIDEO_MB=0
UPLOAD_HIGHLIGHT_VIDEO_MAX_SECONDS=0
ENV

cd "$REMOTE_APP"
php82 /usr/local/bin/composer install --no-dev --optimize-autoloader --no-interaction 2>&1 | tail -25
php82 artisan key:generate --force
php82 artisan storage:link 2>/dev/null || true
php82 artisan config:cache
php82 artisan route:cache
php82 artisan view:cache

chmod -R u+rwX storage bootstrap/cache

cat > public/.htaccess <<'HTA'
<IfModule mod_rewrite.c>
    <IfModule mod_negotiation.c>
        Options -MultiViews -Indexes
    </IfModule>
    RewriteEngine On
    RewriteCond %{{HTTP:Authorization}} .
    RewriteRule .* - [E=HTTP_AUTHORIZATION:%{{HTTP:Authorization}}]
    RewriteCond %{{REQUEST_FILENAME}} !-d
    RewriteCond %{{REQUEST_URI}} (.+)/$
    RewriteRule ^ %1 [L,R=301]
    RewriteCond %{{REQUEST_FILENAME}} !-d
    RewriteCond %{{REQUEST_FILENAME}} !-f
    RewriteRule ^ index.php [L]
</IfModule>
AddHandler application/x-httpd-php82 .php
HTA

php82 artisan --version
curl -s -o /dev/null -w "home=%{{http_code}}\\n" "$APP_URL/" || true
curl -s -o /dev/null -w "login=%{{http_code}}\\n" "$APP_URL/console/login" || true
echo DEPLOY_DONE
"""

local = os.path.join(os.path.expanduser("~"), "OneDrive", "Desktop", "MOA_backup_20260920_190038", "_fix_deploy.sh")
with open(local, "w", newline="\n") as f:
    f.write(deploy_script)

subprocess.run(["scp", "-i", SSH_KEY, local, f"{SSH_HOST}:{REMOTE_UPLOAD}/fix_deploy.sh"], check=True)
r = subprocess.run(
    ["ssh", "-i", SSH_KEY, SSH_HOST, f"chmod +x {REMOTE_UPLOAD}/fix_deploy.sh && bash {REMOTE_UPLOAD}/fix_deploy.sh"],
    capture_output=True,
    text=True,
    timeout=1800,
)
print(r.stdout)
if r.stderr:
    print(r.stderr, file=sys.stderr)
sys.exit(r.returncode)
