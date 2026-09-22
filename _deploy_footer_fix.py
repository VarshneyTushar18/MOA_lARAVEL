import subprocess
import os

key = os.path.expanduser("~/.ssh/id_ed25519")
host = "phi-ltbi-aiia.in@ssh.gb.stackcp.com"
base = os.path.dirname(__file__)
remote_up = "/home/sites/41b/b/ba690bc503/moa_deploy_upload"

script = """#!/bin/bash
set -e
HOME_DIR="/home/sites/41b/b/ba690bc503"
APP="$HOME_DIR/MOA_lARAVEL"
UP="$HOME_DIR/moa_deploy_upload"
cp "$UP/frontend.blade.php" "$APP/resources/views/layout/frontend.blade.php"
cd "$APP"
php82 artisan view:clear
echo DONE
grep -n 'social-media' "$APP/resources/views/layout/frontend.blade.php" || echo social-media-removed
"""

script_path = os.path.join(base, "_deploy_footer_fix.sh")
with open(script_path, "w", encoding="utf-8", newline="\n") as f:
    f.write(script)

blade = os.path.join(base, "resources", "views", "layout", "frontend.blade.php")

for local, remote in [
    (script_path, f"{remote_up}/deploy_footer_fix.sh"),
    (blade, f"{remote_up}/frontend.blade.php"),
]:
    r = subprocess.run(
        ["scp", "-i", key, "-o", "StrictHostKeyChecking=accept-new", local, f"{host}:{remote}"],
        capture_output=True,
        text=True,
        timeout=180,
    )
    print("scp", os.path.basename(local), r.returncode, r.stderr.strip())
    if r.returncode != 0:
        raise SystemExit(1)

r2 = subprocess.run(
    ["ssh", "-i", key, "-o", "StrictHostKeyChecking=accept-new", host, f"bash {remote_up}/deploy_footer_fix.sh"],
    capture_output=True,
    text=True,
    timeout=180,
)
print(r2.stdout)
print(r2.stderr)
print("exit", r2.returncode)
raise SystemExit(r2.returncode)
