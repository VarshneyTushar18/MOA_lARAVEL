import subprocess
import os

script = r"""#!/bin/bash
HOME_DIR="/home/sites/41b/b/ba690bc503"
PUB="$HOME_DIR/public_html"
APP="$HOME_DIR/MOA_lARAVEL"
echo "=== public_html .user.ini ==="
cat "$PUB/.user.ini" 2>/dev/null || echo "missing"
ls -la "$PUB/.user.ini"* 2>/dev/null || true
echo "=== MOA public .user.ini ==="
cat "$APP/public/.user.ini" 2>/dev/null || echo "missing"
echo "=== phpinfo via web would differ; CLI ==="
php82 -r 'echo ini_get("upload_max_filesize")." ".ini_get("post_max_size")."\n";'
echo "=== disk quota ==="
df -h "$APP"
du -sh "$APP/storage/app/public" 2>/dev/null
echo "=== recent page_sections files ==="
find "$APP/storage/app/public/page_sections" -type f 2>/dev/null | wc -l
ls -lt "$APP/storage/app/public/page_sections" 2>/dev/null | head -5
"""

base = os.path.dirname(__file__)
path = os.path.join(base, "_audit_upload.sh")
with open(path, "w", encoding="utf-8", newline="\n") as f:
    f.write(script)

key = os.path.expanduser("~/.ssh/id_ed25519")
host = "phi-ltbi-aiia.in@ssh.gb.stackcp.com"
remote = "/home/sites/41b/b/ba690bc503/moa_deploy_upload/audit_upload.sh"
subprocess.run(["scp", "-i", key, path, f"{host}:{remote}"], check=True)
r = subprocess.run(["ssh", "-i", key, host, f"bash {remote}"], capture_output=True, text=True, timeout=120)
print(r.stdout)
print(r.stderr)
