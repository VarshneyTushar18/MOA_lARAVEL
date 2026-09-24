import os
import subprocess

KEY = os.path.expanduser("~/.ssh/id_ed25519")
HOST = "phi-ltbi-aiia.in@ssh.gb.stackcp.com"
REMOTE_UP = "/home/sites/41b/b/ba690bc503/moa_deploy_upload"
APP = "/home/sites/41b/b/ba690bc503/MOA_lARAVEL"
PUBLIC_HTML = "/home/sites/41b/b/ba690bc503/public_html"

files = [
    ("app/Support/MediaYearResolver.php", "MediaYearResolver.php"),
    ("resources/views/partials/acsm-section.blade.php", "acsm-section.blade.php"),
    ("resources/views/partials/acsm-tb-pledge-year-tabs.blade.php", "acsm-tb-pledge-year-tabs.blade.php"),
    ("resources/views/partials/acsm-image-gallery.blade.php", "acsm-image-gallery.blade.php"),
    ("resources/views/partials/acsm-video-gallery.blade.php", "acsm-video-gallery.blade.php"),
    ("resources/views/pages/acsm_iec.blade.php", "acsm_iec.blade.php"),
    ("resources/views/layout/frontend.blade.php", "frontend.blade.php"),
    ("public/assets/css/main.css", "main.css"),
]

script = f"""#!/bin/bash
set -e
cp {REMOTE_UP}/MediaYearResolver.php {APP}/app/Support/MediaYearResolver.php
cp {REMOTE_UP}/acsm-section.blade.php {APP}/resources/views/partials/acsm-section.blade.php
cp {REMOTE_UP}/acsm-tb-pledge-year-tabs.blade.php {APP}/resources/views/partials/acsm-tb-pledge-year-tabs.blade.php
cp {REMOTE_UP}/acsm-image-gallery.blade.php {APP}/resources/views/partials/acsm-image-gallery.blade.php
cp {REMOTE_UP}/acsm-video-gallery.blade.php {APP}/resources/views/partials/acsm-video-gallery.blade.php
cp {REMOTE_UP}/acsm_iec.blade.php {APP}/resources/views/pages/acsm_iec.blade.php
cp {REMOTE_UP}/frontend.blade.php {APP}/resources/views/layout/frontend.blade.php
cp {REMOTE_UP}/main.css {APP}/public/assets/css/main.css
cp {REMOTE_UP}/main.css {PUBLIC_HTML}/assets/css/main.css
cd {APP}
php82 artisan view:clear
php82 artisan view:cache
echo DONE
"""

base = os.path.dirname(__file__)
with open(os.path.join(base, "_deploy_tb_pledge_year_tabs.sh"), "w", encoding="utf-8", newline="\n") as f:
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

subprocess.run(["scp", "-i", KEY, os.path.join(base, "_deploy_tb_pledge_year_tabs.sh"), f"{HOST}:{REMOTE_UP}/deploy_tb_pledge_year_tabs.sh"], check=True)
r2 = subprocess.run(["ssh", "-i", KEY, HOST, f"bash {REMOTE_UP}/deploy_tb_pledge_year_tabs.sh"], capture_output=True, text=True, timeout=120000)
print(r2.stdout)
print(r2.stderr)
if r2.returncode != 0:
    raise SystemExit(r2.returncode)
