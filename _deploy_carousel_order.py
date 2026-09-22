import subprocess
import os

key = os.path.expanduser("~/.ssh/id_ed25519")
host = "phi-ltbi-aiia.in@ssh.gb.stackcp.com"
base = os.path.dirname(__file__)
remote_up = "/home/sites/41b/b/ba690bc503/moa_deploy_upload"
app = "/home/sites/41b/b/ba690bc503/MOA_lARAVEL"
pub = "/home/sites/41b/b/ba690bc503/public_html"

uploads = [
    ("app/Services/HeroCarouselService.php", "HeroCarouselService.php", f"{app}/app/Services/HeroCarouselService.php"),
    ("app/Http/Controllers/PageSectionsController.php", "PageSectionsController.php", f"{app}/app/Http/Controllers/PageSectionsController.php"),
    ("resources/views/pages/home.blade.php", "home.blade.php", f"{app}/resources/views/pages/home.blade.php"),
    ("resources/views/pages_console/sections/edit.blade.php", "sections_edit.blade.php", f"{app}/resources/views/pages_console/sections/edit.blade.php"),
    ("resources/views/layout/frontend.blade.php", "frontend.blade.php", f"{app}/resources/views/layout/frontend.blade.php"),
    ("public/assets/css/main.css", "main.css", None),
]

script = f"""#!/bin/bash
set -e
APP="{app}"
PUB="{pub}"
UP="{remote_up}"
mkdir -p "$APP/app/Services"
cp "$UP/HeroCarouselService.php" "$APP/app/Services/HeroCarouselService.php"
cp "$UP/PageSectionsController.php" "$APP/app/Http/Controllers/PageSectionsController.php"
cp "$UP/home.blade.php" "$APP/resources/views/pages/home.blade.php"
cp "$UP/sections_edit.blade.php" "$APP/resources/views/pages_console/sections/edit.blade.php"
cp "$UP/frontend.blade.php" "$APP/resources/views/layout/frontend.blade.php"
cp "$UP/main.css" "$APP/public/assets/css/main.css"
cp "$UP/main.css" "$PUB/assets/css/main.css"
cd "$APP"
php82 artisan view:clear
echo DONE
"""

script_path = os.path.join(base, "_deploy_carousel_order.sh")
with open(script_path, "w", encoding="utf-8", newline="\n") as f:
    f.write(script)

for local_rel, remote_name, _ in uploads:
    local = os.path.join(base, local_rel.replace("/", os.sep))
    r = subprocess.run(
        ["scp", "-i", key, "-o", "StrictHostKeyChecking=accept-new", local, f"{host}:{remote_up}/{remote_name}"],
        capture_output=True,
        text=True,
        timeout=180,
    )
    print("scp", remote_name, r.returncode, r.stderr.strip())
    if r.returncode != 0:
        raise SystemExit(1)

subprocess.run(["scp", "-i", key, "-o", "StrictHostKeyChecking=accept-new", script_path, f"{host}:{remote_up}/deploy_carousel_order.sh"], check=True)
r2 = subprocess.run(["ssh", "-i", key, "-o", "StrictHostKeyChecking=accept-new", host, f"bash {remote_up}/deploy_carousel_order.sh"], capture_output=True, text=True, timeout=180)
print(r2.stdout)
print(r2.stderr)
print("exit", r2.returncode)
