import os
import subprocess

key = os.path.expanduser("~/.ssh/id_ed25519")
host = "phi-ltbi-aiia.in@ssh.gb.stackcp.com"
base = os.path.dirname(__file__)
remote_up = "/home/sites/41b/b/ba690bc503/moa_deploy_upload"
app = "/home/sites/41b/b/ba690bc503/MOA_lARAVEL"
pub = "/home/sites/41b/b/ba690bc503/public_html"

uploads = [
    ("app/Support/MediaGalleryRules.php", "MediaGalleryRules.php"),
    ("resources/views/partials/acsm-image-gallery.blade.php", "acsm-image-gallery.blade.php"),
    ("resources/views/partials/acsm-video-gallery.blade.php", "acsm-video-gallery.blade.php"),
    ("resources/views/partials/acsm-section.blade.php", "acsm-section.blade.php"),
    ("resources/views/partials/acsm-gallery-scripts.blade.php", "acsm-gallery-scripts.blade.php"),
    ("resources/views/pages/acsm_iec.blade.php", "acsm_iec.blade.php"),
    ("resources/views/pages/best_practices.blade.php", "best_practices.blade.php"),
    ("resources/views/pages/performance_report.blade.php", "performance_report.blade.php"),
    ("resources/views/pages/factsheet.blade.php", "factsheet.blade.php"),
    ("public/assets/css/main.css", "main.css"),
    ("resources/views/layout/frontend.blade.php", "frontend.blade.php"),
]

script = f"""#!/bin/bash
set -e
APP="{app}"
PUB="{pub}"
UP="{remote_up}"
mkdir -p "$APP/app/Support" "$APP/resources/views/partials" "$APP/resources/views/pages"
cp "$UP/MediaGalleryRules.php" "$APP/app/Support/MediaGalleryRules.php"
cp "$UP/acsm-image-gallery.blade.php" "$APP/resources/views/partials/acsm-image-gallery.blade.php"
cp "$UP/acsm-video-gallery.blade.php" "$APP/resources/views/partials/acsm-video-gallery.blade.php"
cp "$UP/acsm-section.blade.php" "$APP/resources/views/partials/acsm-section.blade.php"
cp "$UP/acsm-gallery-scripts.blade.php" "$APP/resources/views/partials/acsm-gallery-scripts.blade.php"
cp "$UP/acsm_iec.blade.php" "$APP/resources/views/pages/acsm_iec.blade.php"
cp "$UP/best_practices.blade.php" "$APP/resources/views/pages/best_practices.blade.php"
cp "$UP/performance_report.blade.php" "$APP/resources/views/pages/performance_report.blade.php"
cp "$UP/factsheet.blade.php" "$APP/resources/views/pages/factsheet.blade.php"
cp "$UP/frontend.blade.php" "$APP/resources/views/layout/frontend.blade.php"
cp "$UP/main.css" "$APP/public/assets/css/main.css"
cp "$UP/main.css" "$PUB/assets/css/main.css"
cd "$APP"
php82 artisan view:clear
php82 artisan view:cache
echo DONE
"""

script_path = os.path.join(base, "_deploy_gallery_threshold.sh")
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
    ["scp", "-i", key, "-o", "StrictHostKeyChecking=accept-new", script_path, f"{host}:{remote_up}/deploy_gallery_threshold.sh"],
    check=True,
)
r2 = subprocess.run(["ssh", "-i", key, host, f"bash {remote_up}/deploy_gallery_threshold.sh"], capture_output=True, text=True, timeout=180)
print(r2.stdout)
print(r2.stderr)
