import subprocess
import os

fix = r"""#!/bin/bash
set -e
HOME_DIR="/home/sites/41b/b/ba690bc503"
APP="$HOME_DIR/MOA_lARAVEL"
PUB="$HOME_DIR/public_html"

cd "$APP"

echo "=== Before ==="
ls -la "$APP/public/storage" 2>&1 || echo "MOA public/storage missing"
ls -la "$PUB/storage" 2>&1 || echo "public_html/storage missing"
ls -ld "$APP/storage" "$APP/storage/app" "$APP/storage/app/public" 2>&1

echo "=== Fix permissions ==="
mkdir -p "$APP/storage/app/public/page_sections"
chmod -R u+rwX,g+rwX "$APP/storage" "$APP/bootstrap/cache" 2>/dev/null || chmod -R u+rwX "$APP/storage" "$APP/bootstrap/cache"

echo "=== Fix symlinks ==="
rm -f "$APP/public/storage"
ln -sfn "$APP/storage/app/public" "$APP/public/storage"
rm -f "$PUB/storage"
ln -sfn "$APP/storage/app/public" "$PUB/storage"

echo "=== storage:link ==="
php82 artisan storage:link 2>/dev/null || true

echo "=== Write test ==="
touch "$APP/storage/app/public/.upload_test" && rm -f "$APP/storage/app/public/.upload_test" && echo "write OK"

echo "=== After ==="
ls -la "$APP/public/storage"
ls -la "$PUB/storage"
php82 -r 'echo "upload=".ini_get("upload_max_filesize")." post=".ini_get("post_max_size")."\n";'
echo DONE
"""

base = os.path.dirname(__file__)
path = os.path.join(base, "_fix_upload_storage.sh")
with open(path, "w", encoding="utf-8", newline="\n") as f:
    f.write(fix)

key = os.path.expanduser("~/.ssh/id_ed25519")
host = "phi-ltbi-aiia.in@ssh.gb.stackcp.com"
remote = "/home/sites/41b/b/ba690bc503/moa_deploy_upload/fix_upload_storage.sh"
subprocess.run(["scp", "-i", key, "-o", "StrictHostKeyChecking=accept-new", path, f"{host}:{remote}"], check=True)
r = subprocess.run(["ssh", "-i", key, "-o", "StrictHostKeyChecking=accept-new", host, f"bash {remote}"], capture_output=True, text=True, timeout=120)
print(r.stdout)
print(r.stderr)
