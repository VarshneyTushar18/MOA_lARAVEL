import subprocess
import os

key = os.path.expanduser("~/.ssh/id_ed25519")
host = "phi-ltbi-aiia.in@ssh.gb.stackcp.com"
base = os.path.dirname(__file__)
remote_up = "/home/sites/41b/b/ba690bc503/moa_deploy_upload"
app = "/home/sites/41b/b/ba690bc503/MOA_lARAVEL"
public_html = "/home/sites/41b/b/ba690bc503/public_html"

pairs = [
    ("public/assets/css/main.css", "main.css"),
    ("resources/views/pages/home.blade.php", "home.blade.php"),
]

script = f"""#!/bin/bash
set -e
cp {remote_up}/main.css {app}/public/assets/css/main.css
cp {remote_up}/main.css {public_html}/assets/css/main.css
cp {remote_up}/home.blade.php {app}/resources/views/pages/home.blade.php
cd {app}
php82 artisan view:clear
php82 -r "if (function_exists('opcache_reset')) opcache_reset();"
echo DONE
"""

script_path = os.path.join(base, "_deploy_gallery_albums.sh")
with open(script_path, "w", encoding="utf-8", newline="\n") as f:
    f.write(script)

for local_rel, remote_name in pairs:
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
    ["scp", "-i", key, "-o", "StrictHostKeyChecking=accept-new", script_path, f"{host}:{remote_up}/deploy_gallery_albums.sh"],
    check=True,
)
r2 = subprocess.run(
    ["ssh", "-i", key, host, f"bash {remote_up}/deploy_gallery_albums.sh"],
    capture_output=True,
    text=True,
    timeout=120000,
)
print(r2.stdout)
print(r2.stderr)
if r2.returncode != 0:
    raise SystemExit(r2.returncode)
