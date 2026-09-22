import subprocess
import os

key = os.path.expanduser("~/.ssh/id_ed25519")
host = "phi-ltbi-aiia.in@ssh.gb.stackcp.com"
base = os.path.dirname(__file__)
remote_up = "/home/sites/41b/b/ba690bc503/moa_deploy_upload"
app = "/home/sites/41b/b/ba690bc503/MOA_lARAVEL"
pub = "/home/sites/41b/b/ba690bc503/public_html"

uploads = [
    ("app/Http/Controllers/PageSectionsController.php", "PageSectionsController.php"),
    ("resources/views/pages_console/sections/edit.blade.php", "sections_edit.blade.php"),
    ("resources/views/pages_console/sections/add.blade.php", "sections_add.blade.php"),
    ("public/assets/js/console-upload-progress.js", "console-upload-progress.js"),
    ("resources/views/layout/console.blade.php", "console.blade.php"),
]

script = f"""#!/bin/bash
set -e
APP="{app}"
PUB="{pub}"
UP="{remote_up}"
cp "$UP/PageSectionsController.php" "$APP/app/Http/Controllers/PageSectionsController.php"
cp "$UP/sections_edit.blade.php" "$APP/resources/views/pages_console/sections/edit.blade.php"
cp "$UP/sections_add.blade.php" "$APP/resources/views/pages_console/sections/add.blade.php"
cp "$UP/console-upload-progress.js" "$APP/public/assets/js/console-upload-progress.js"
cp "$UP/console-upload-progress.js" "$PUB/assets/js/console-upload-progress.js"
cp "$UP/console.blade.php" "$APP/resources/views/layout/console.blade.php"
cd "$APP"
php82 artisan view:clear
echo DONE
"""

script_path = os.path.join(base, "_deploy_upload422_fix.sh")
with open(script_path, "w", encoding="utf-8", newline="\n") as f:
    f.write(script)

for local_rel, remote_name in uploads:
    local = os.path.join(base, local_rel.replace("/", os.sep))
    r = subprocess.run(["scp", "-i", key, "-o", "StrictHostKeyChecking=accept-new", local, f"{host}:{remote_up}/{remote_name}"], capture_output=True, text=True, timeout=180)
    print("scp", remote_name, r.returncode, r.stderr.strip() or "ok")
    if r.returncode != 0:
        raise SystemExit(1)

subprocess.run(["scp", "-i", key, "-o", "StrictHostKeyChecking=accept-new", script_path, f"{host}:{remote_up}/deploy_upload422_fix.sh"], check=True)
r2 = subprocess.run(["ssh", "-i", key, host, f"bash {remote_up}/deploy_upload422_fix.sh"], capture_output=True, text=True, timeout=180)
print(r2.stdout)
print(r2.stderr)
