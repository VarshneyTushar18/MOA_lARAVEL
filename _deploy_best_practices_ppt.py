import os
import subprocess

KEY = os.path.expanduser("~/.ssh/id_ed25519")
HOST = "phi-ltbi-aiia.in@ssh.gb.stackcp.com"
REMOTE_UP = "/home/sites/41b/b/ba690bc503/moa_deploy_upload"
APP = "/home/sites/41b/b/ba690bc503/MOA_lARAVEL"

files = [
    ("resources/views/pages/best_practices.blade.php", "best_practices.blade.php"),
    ("resources/views/partials/pdf-card.blade.php", "pdf-card.blade.php"),
    ("app/Http/Controllers/PageSectionsController.php", "PageSectionsController.php"),
    ("resources/views/pages_console/sections/edit.blade.php", "sections_edit.blade.php"),
    ("resources/views/pages_console/sections/add.blade.php", "sections_add.blade.php"),
    ("resources/views/pages_console/sections/list.blade.php", "sections_list.blade.php"),
]

script = f"""#!/bin/bash
set -e
cp {REMOTE_UP}/best_practices.blade.php {APP}/resources/views/pages/best_practices.blade.php
cp {REMOTE_UP}/pdf-card.blade.php {APP}/resources/views/partials/pdf-card.blade.php
cp {REMOTE_UP}/PageSectionsController.php {APP}/app/Http/Controllers/PageSectionsController.php
cp {REMOTE_UP}/sections_edit.blade.php {APP}/resources/views/pages_console/sections/edit.blade.php
cp {REMOTE_UP}/sections_add.blade.php {APP}/resources/views/pages_console/sections/add.blade.php
cp {REMOTE_UP}/sections_list.blade.php {APP}/resources/views/pages_console/sections/list.blade.php
cd {APP}
php82 artisan view:clear
php82 artisan view:cache
php82 artisan route:clear
echo DONE
"""

base = os.path.dirname(__file__)
with open(os.path.join(base, "_deploy_best_practices_ppt.sh"), "w", encoding="utf-8", newline="\n") as f:
    f.write(script)

for local_rel, remote in files:
    local = os.path.join(base, local_rel.replace("/", os.sep))
    r = subprocess.run(
        ["scp", "-i", KEY, local, f"{HOST}:{REMOTE_UP}/{remote}"],
        capture_output=True, text=True, timeout=180,
    )
    print("scp", remote, r.returncode, r.stderr.strip() or "ok")
    if r.returncode != 0:
        raise SystemExit(r.returncode)

subprocess.run(
    ["scp", "-i", KEY, os.path.join(base, "_deploy_best_practices_ppt.sh"), f"{HOST}:{REMOTE_UP}/deploy_best_practices_ppt.sh"],
    check=True,
)
r2 = subprocess.run(
    ["ssh", "-i", KEY, HOST, f"bash {REMOTE_UP}/deploy_best_practices_ppt.sh"],
    capture_output=True, text=True, timeout=120000,
)
print(r2.stdout)
print(r2.stderr)
if r2.returncode != 0:
    raise SystemExit(r2.returncode)
