import subprocess
import os

key = os.path.expanduser("~/.ssh/id_ed25519")
host = "phi-ltbi-aiia.in@ssh.gb.stackcp.com"
base = os.path.dirname(__file__)
remote_up = "/home/sites/41b/b/ba690bc503/moa_deploy_upload"
app = "/home/sites/41b/b/ba690bc503/MOA_lARAVEL"
pub = "/home/sites/41b/b/ba690bc503/public_html"

uploads = [
    ("resources/views/layout/frontend.blade.php", "frontend.blade.php"),
    ("resources/views/pages/home.blade.php", "home.blade.php"),
    ("public/assets/css/main.css", "main.css"),
]

script = f"""#!/bin/bash
set -e
APP="{app}"
PUB="{pub}"
UP="{remote_up}"
mkdir -p "$APP/resources/views/layout" "$APP/resources/views/pages" "$APP/public/assets/css" "$PUB/assets/css"
cp "$UP/frontend.blade.php" "$APP/resources/views/layout/frontend.blade.php"
cp "$UP/home.blade.php" "$APP/resources/views/pages/home.blade.php"
cp "$UP/main.css" "$APP/public/assets/css/main.css"
cp "$UP/main.css" "$PUB/assets/css/main.css"
cd "$APP"
php82 artisan view:clear
php82 -r "if (function_exists('opcache_reset')) opcache_reset();"
echo DONE
"""

script_path = os.path.join(base, "_deploy_moa_highlight.sh")
with open(script_path, "w", encoding="utf-8", newline="\n") as f:
    f.write(script)

for local_rel, remote_name in uploads:
    local = os.path.join(base, local_rel.replace("/", os.sep))
    r = subprocess.run(
        ["scp", "-i", key, "-o", "StrictHostKeyChecking=accept-new", local, f"{host}:{remote_up}/{remote_name}"],
        capture_output=True,
        text=True,
        timeout=180,
    )
    print("scp", remote_name, r.returncode, r.stderr.strip() or "ok")
    if r.returncode != 0:
        raise SystemExit(1)

subprocess.run(
    ["scp", "-i", key, "-o", "StrictHostKeyChecking=accept-new", script_path, f"{host}:{remote_up}/deploy_moa_highlight.sh"],
    check=True,
)
r2 = subprocess.run(
    ["ssh", "-i", key, host, f"bash {remote_up}/deploy_moa_highlight.sh"],
    capture_output=True,
    text=True,
    timeout=180,
)
print(r2.stdout)
print(r2.stderr)
