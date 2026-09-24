import os
import subprocess

KEY = os.path.expanduser("~/.ssh/id_ed25519")
HOST = "phi-ltbi-aiia.in@ssh.gb.stackcp.com"
REMOTE_UP = "/home/sites/41b/b/ba690bc503/moa_deploy_upload"
APP = "/home/sites/41b/b/ba690bc503/MOA_lARAVEL"

files = [
    ("app/Http/Controllers/PageSectionsController.php", "PageSectionsController.php"),
    ("app/Models/PageSection.php", "PageSection.php"),
    ("app/Models/PageSectionMedia.php", "PageSectionMedia.php"),
    ("database/migrations/2026_09_24_100000_add_sort_order_to_page_section_media_table.php", "migration_sort_order_media.php"),
    ("resources/views/pages_console/sections/edit.blade.php", "sections_edit.blade.php"),
    ("resources/views/pages_console/sections/list.blade.php", "sections_list.blade.php"),
    ("resources/views/pages/acsm_iec.blade.php", "acsm_iec.blade.php"),
    ("resources/views/partials/acsm-section.blade.php", "acsm-section.blade.php"),
    ("resources/views/partials/acsm-video-gallery.blade.php", "acsm-video-gallery.blade.php"),
]

script = f"""#!/bin/bash
set -e
cp {REMOTE_UP}/PageSectionsController.php {APP}/app/Http/Controllers/PageSectionsController.php
cp {REMOTE_UP}/PageSection.php {APP}/app/Models/PageSection.php
cp {REMOTE_UP}/PageSectionMedia.php {APP}/app/Models/PageSectionMedia.php
cp {REMOTE_UP}/migration_sort_order_media.php {APP}/database/migrations/2026_09_24_100000_add_sort_order_to_page_section_media_table.php
cp {REMOTE_UP}/sections_edit.blade.php {APP}/resources/views/pages_console/sections/edit.blade.php
cp {REMOTE_UP}/sections_list.blade.php {APP}/resources/views/pages_console/sections/list.blade.php
cp {REMOTE_UP}/acsm_iec.blade.php {APP}/resources/views/pages/acsm_iec.blade.php
cp {REMOTE_UP}/acsm-section.blade.php {APP}/resources/views/partials/acsm-section.blade.php
cp {REMOTE_UP}/acsm-video-gallery.blade.php {APP}/resources/views/partials/acsm-video-gallery.blade.php
cd {APP}
php82 artisan migrate --force
php82 artisan view:clear
php82 artisan view:cache
php82 -r "if (function_exists('opcache_reset')) opcache_reset();"
echo DONE
"""

base = os.path.dirname(__file__)
script_path = os.path.join(base, "_deploy_media_fix.sh")
with open(script_path, "w", encoding="utf-8", newline="\n") as f:
    f.write(script)

for local_rel, remote in files:
    local = os.path.join(base, local_rel.replace("/", os.sep))
    r = subprocess.run(
        ["scp", "-i", KEY, "-o", "StrictHostKeyChecking=accept-new", local, f"{HOST}:{REMOTE_UP}/{remote}"],
        capture_output=True,
        text=True,
        timeout=180,
    )
    print("scp", remote, r.returncode, r.stderr.strip() or "ok")
    if r.returncode != 0:
        raise SystemExit(r.returncode)

subprocess.run(["scp", "-i", KEY, script_path, f"{HOST}:{REMOTE_UP}/deploy_media_fix.sh"], check=True)
r2 = subprocess.run(
    ["ssh", "-i", KEY, HOST, f"bash {REMOTE_UP}/deploy_media_fix.sh"],
    capture_output=True,
    text=True,
    timeout=180000,
)
print(r2.stdout)
print(r2.stderr)
if r2.returncode != 0:
    raise SystemExit(r2.returncode)
