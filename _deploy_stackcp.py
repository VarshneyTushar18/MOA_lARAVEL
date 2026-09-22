#!/usr/bin/env python3
"""Deploy MOA backup to StackCP server."""
import os
import subprocess
import sys
import time

SSH_KEY = os.path.join(os.path.expanduser("~"), ".ssh", "id_ed25519")
SSH_HOST = "phi-ltbi-aiia.in@ssh.gb.stackcp.com"
BACKUP = os.path.join(
    os.path.expanduser("~"),
    "OneDrive",
    "Desktop",
    "MOA_backup_20260920_190038",
)
TAR = os.path.join(BACKUP, "MOA_lARAVEL_20260920_190038.tar.gz")
SQL = os.path.join(BACKUP, "moa_db_20260920_190038.sql")
REMOTE_HOME = "/home/sites/41b/b/ba690bc503"
REMOTE_APP = f"{REMOTE_HOME}/MOA_lARAVEL"
REMOTE_UPLOAD = f"{REMOTE_HOME}/moa_deploy_upload"
DB_HOST = "sdb-79.hosting.stackcp.net"
DB_NAME = "wordpress-35303833ad48"  # only DB available on this account
DB_USER = "wordpress-35303833ad48"
DB_PASS = "9a947ecd5f067145"
APP_URL = "https://phi-ltbi-aiia.in"


def ssh(cmd: str, timeout: int = 600) -> subprocess.CompletedProcess:
    return subprocess.run(
        ["ssh", "-i", SSH_KEY, "-o", "StrictHostKeyChecking=accept-new", SSH_HOST, cmd],
        capture_output=True,
        text=True,
        timeout=timeout,
    )


def scp(local: str, remote: str, timeout: int = 7200) -> subprocess.CompletedProcess:
    return subprocess.run(
        ["scp", "-i", SSH_KEY, "-o", "StrictHostKeyChecking=accept-new", local, f"{SSH_HOST}:{remote}"],
        capture_output=True,
        text=True,
        timeout=timeout,
    )


def run_step(label: str, fn):
    print(f"\n=== {label} ===")
    fn()
    print("OK")


def main():
    if not os.path.isfile(TAR):
        print("Missing tar:", TAR, file=sys.stderr)
        sys.exit(1)
    if not os.path.isfile(SQL):
        print("Missing sql:", SQL, file=sys.stderr)
        sys.exit(1)

    run_step("Prepare remote dirs", lambda: ssh(
        f"mkdir -p {REMOTE_UPLOAD} && rm -rf {REMOTE_APP} && mkdir -p {REMOTE_APP}"
    ).check_returncode())

    run_step("Upload SQL dump (~3 MB)", lambda: scp(SQL, f"{REMOTE_UPLOAD}/moa_db.sql", 300).check_returncode())

    print("\n=== Upload project archive (~1.3 GB, may take 10-30 min) ===")
    t0 = time.time()
    r = scp(TAR, f"{REMOTE_UPLOAD}/moa_project.tar.gz", 7200)
    if r.returncode != 0:
        print(r.stderr, file=sys.stderr)
        sys.exit(1)
    print(f"Upload done in {(time.time()-t0)/60:.1f} min")

    deploy_script = rf"""#!/bin/bash
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

echo "Backing up WordPress public_html..."
if [ -d public_html_wp_backup ]; then rm -rf public_html_wp_backup
fi
mv public_html public_html_wp_backup

echo "Extracting MOA..."
mkdir -p "$REMOTE_APP"
tar -xzf "$REMOTE_UPLOAD/moa_project.tar.gz" -C "$REMOTE_HOME"
if [ -d "$REMOTE_HOME/MOA_lARAVEL" ]; then
  rm -rf "$REMOTE_APP"
  mv "$REMOTE_HOME/MOA_lARAVEL" "$REMOTE_APP"
fi

echo "Linking public_html -> MOA public"
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
UPLOAD_VIDEO_COMPRESSION=true
UPLOAD_VIDEO_COMPRESS_ASYNC=false
UPLOAD_MAX_VIDEO_MB=0
UPLOAD_HIGHLIGHT_VIDEO_MAX_SECONDS=0
ENV

cd "$REMOTE_APP"

echo "Composer install..."
php82 /usr/local/bin/composer install --no-dev --optimize-autoloader --no-interaction 2>&1 | tail -20

echo "Artisan setup..."
php82 artisan key:generate --force
php82 artisan storage:link || true
php82 artisan config:cache
php82 artisan route:cache
php82 artisan view:cache

echo "Permissions..."
chmod -R u+rwX "$REMOTE_APP/storage" "$REMOTE_APP/bootstrap/cache"
find "$REMOTE_APP/storage" -type d -exec chmod 775 {{}} \;
find "$REMOTE_APP/bootstrap/cache" -type d -exec chmod 775 {{}} \;

echo "PHP version marker for StackCP..."
cat > "$REMOTE_APP/public/.htaccess" <<'HTA'
<IfModule mod_rewrite.c>
    <IfModule mod_negotiation.c>
        Options -MultiViews -Indexes
    </IfModule>

    RewriteEngine On

    # Handle Authorization Header
    RewriteCond %{{HTTP:Authorization}} .
    RewriteRule .* - [E=HTTP_AUTHORIZATION:%{{HTTP:Authorization}}]

    # Redirect Trailing Slashes If Not A Folder...
    RewriteCond %{{REQUEST_FILENAME}} !-d
    RewriteCond %{{REQUEST_URI}} (.+)/$
    RewriteRule ^ %1 [L,R=301]

    # Send Requests To Front Controller...
    RewriteCond %{{REQUEST_FILENAME}} !-d
    RewriteCond %{{REQUEST_FILENAME}} !-f
    RewriteRule ^ index.php [L]
</IfModule>

AddHandler application/x-httpd-php82 .php
HTA

echo "Smoke test..."
php82 artisan --version
curl -s -o /dev/null -w "HTTP %{{http_code}}\\n" "$APP_URL/" || true
curl -s -o /dev/null -w "HTTP %{{http_code}}\\n" "$APP_URL/console/login" || true

echo "DEPLOY_DONE"
"""

    local_script = os.path.join(BACKUP, "_remote_deploy.sh")
    with open(local_script, "w", newline="\n") as f:
        f.write(deploy_script)

    run_step("Upload deploy script", lambda: scp(local_script, f"{REMOTE_UPLOAD}/deploy.sh", 120).check_returncode())

    print("\n=== Run remote deploy ===")
    r = ssh(f"chmod +x {REMOTE_UPLOAD}/deploy.sh && bash {REMOTE_UPLOAD}/deploy.sh", timeout=1800)
    print(r.stdout)
    if r.stderr:
        print(r.stderr, file=sys.stderr)
    if r.returncode != 0:
        sys.exit(r.returncode)

    if "DEPLOY_DONE" not in r.stdout:
        print("Deploy may have failed - DEPLOY_DONE not seen", file=sys.stderr)
        sys.exit(1)

    print(f"\nSite should be live at {APP_URL}")


if __name__ == "__main__":
    main()
