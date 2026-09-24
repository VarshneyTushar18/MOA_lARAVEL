import os
import subprocess

KEY = os.path.expanduser("~/.ssh/id_ed25519")
HOST = "phi-ltbi-aiia.in@ssh.gb.stackcp.com"
REMOTE_UP = "/home/sites/41b/b/ba690bc503/moa_deploy_upload"
APP = "/home/sites/41b/b/ba690bc503/MOA_lARAVEL"
PUBLIC_HTML = "/home/sites/41b/b/ba690bc503/public_html"

pairs = [
    ("app/Http/Controllers/PageSectionsController.php", "PageSectionsController.php"),
    ("public/.user.ini", ".user.ini"),
]

script = f"""#!/bin/bash
set -e
cp {REMOTE_UP}/PageSectionsController.php {APP}/app/Http/Controllers/PageSectionsController.php
cp {REMOTE_UP}/.user.ini {APP}/public/.user.ini
cp {REMOTE_UP}/.user.ini {PUBLIC_HTML}/.user.ini
rm -f {PUBLIC_HTML}/php_limits_probe.php
cd {APP}
php82 artisan optimize:clear
php82 artisan view:cache
php82 -r "if (function_exists('opcache_reset')) opcache_reset();"
echo DONE
"""

base = os.path.dirname(__file__)
script_path = os.path.join(base, "_deploy_upload_fix.sh")
with open(script_path, "w", encoding="utf-8", newline="\n") as f:
    f.write(script)

for local_rel, remote_name in pairs:
    local = os.path.join(base, local_rel.replace("/", os.sep))
    r = subprocess.run(
        ["scp", "-i", KEY, "-o", "StrictHostKeyChecking=accept-new", local, f"{HOST}:{REMOTE_UP}/{remote_name}"],
        capture_output=True,
        text=True,
        timeout=180,
    )
    print("scp", remote_name, r.returncode, r.stderr.strip() or "ok")
    if r.returncode != 0:
        raise SystemExit(r.returncode)

subprocess.run(
    ["scp", "-i", KEY, "-o", "StrictHostKeyChecking=accept-new", script_path, f"{HOST}:{REMOTE_UP}/deploy_upload_fix.sh"],
    check=True,
)
r2 = subprocess.run(
    ["ssh", "-i", KEY, HOST, f"bash {REMOTE_UP}/deploy_upload_fix.sh"],
    capture_output=True,
    text=True,
    timeout=120000,
)
print(r2.stdout)
print(r2.stderr)
if r2.returncode != 0:
    raise SystemExit(r2.returncode)
