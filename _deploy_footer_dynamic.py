import subprocess
import os

key = os.path.expanduser("~/.ssh/id_ed25519")
host = "phi-ltbi-aiia.in@ssh.gb.stackcp.com"
base = os.path.dirname(__file__)
remote_up = "/home/sites/41b/b/ba690bc503/moa_deploy_upload"
app = "/home/sites/41b/b/ba690bc503/MOA_lARAVEL"

files = [
    ("app/Services/FooterContentService.php", f"{app}/app/Services/FooterContentService.php"),
    ("app/Http/Controllers/Console/FooterController.php", f"{app}/app/Http/Controllers/Console/FooterController.php"),
    ("app/Providers/AppServiceProvider.php", f"{app}/app/Providers/AppServiceProvider.php"),
    ("app/Http/Controllers/PagesController.php", f"{app}/app/Http/Controllers/PagesController.php"),
    ("routes/web.php", f"{app}/routes/web.php"),
    ("resources/views/partials/site-footer.blade.php", f"{app}/resources/views/partials/site-footer.blade.php"),
    ("resources/views/footer_console/edit.blade.php", f"{app}/resources/views/footer_console/edit.blade.php"),
    ("resources/views/footer_console/partials/link-row.blade.php", f"{app}/resources/views/footer_console/partials/link-row.blade.php"),
    ("resources/views/layout/frontend.blade.php", f"{app}/resources/views/layout/frontend.blade.php"),
    ("resources/views/layout/console.blade.php", f"{app}/resources/views/layout/console.blade.php"),
    ("resources/views/console/dashboard.blade.php", f"{app}/resources/views/console/dashboard.blade.php"),
]

script = """#!/bin/bash
set -e
APP="/home/sites/41b/b/ba690bc503/MOA_lARAVEL"
UP="/home/sites/41b/b/ba690bc503/moa_deploy_upload"

mkdir -p "$APP/app/Services"
mkdir -p "$APP/app/Http/Controllers/Console"
mkdir -p "$APP/resources/views/partials"
mkdir -p "$APP/resources/views/footer_console/partials"

cp "$UP/FooterContentService.php" "$APP/app/Services/FooterContentService.php"
cp "$UP/FooterController.php" "$APP/app/Http/Controllers/Console/FooterController.php"
cp "$UP/AppServiceProvider.php" "$APP/app/Providers/AppServiceProvider.php"
cp "$UP/PagesController.php" "$APP/app/Http/Controllers/PagesController.php"
cp "$UP/web.php" "$APP/routes/web.php"
cp "$UP/site-footer.blade.php" "$APP/resources/views/partials/site-footer.blade.php"
cp "$UP/footer_edit.blade.php" "$APP/resources/views/footer_console/edit.blade.php"
cp "$UP/link-row.blade.php" "$APP/resources/views/footer_console/partials/link-row.blade.php"
cp "$UP/frontend.blade.php" "$APP/resources/views/layout/frontend.blade.php"
cp "$UP/console.blade.php" "$APP/resources/views/layout/console.blade.php"
cp "$UP/dashboard.blade.php" "$APP/resources/views/console/dashboard.blade.php"

cd "$APP"
php82 artisan view:clear
php82 artisan route:clear
php82 artisan config:clear
php82 artisan config:cache
echo DONE
"""

uploads = [
    ("app/Services/FooterContentService.php", "FooterContentService.php"),
    ("app/Http/Controllers/Console/FooterController.php", "FooterController.php"),
    ("app/Providers/AppServiceProvider.php", "AppServiceProvider.php"),
    ("app/Http/Controllers/PagesController.php", "PagesController.php"),
    ("routes/web.php", "web.php"),
    ("resources/views/partials/site-footer.blade.php", "site-footer.blade.php"),
    ("resources/views/footer_console/edit.blade.php", "footer_edit.blade.php"),
    ("resources/views/footer_console/partials/link-row.blade.php", "link-row.blade.php"),
    ("resources/views/layout/frontend.blade.php", "frontend.blade.php"),
    ("resources/views/layout/console.blade.php", "console.blade.php"),
    ("resources/views/console/dashboard.blade.php", "dashboard.blade.php"),
]

script_path = os.path.join(base, "_deploy_footer_dynamic.sh")
with open(script_path, "w", encoding="utf-8", newline="\n") as f:
    f.write(script)

for local_rel, remote_name in uploads:
    local = os.path.join(base, local_rel.replace("/", os.sep))
    remote = f"{remote_up}/{remote_name}"
    r = subprocess.run(
        ["scp", "-i", key, "-o", "StrictHostKeyChecking=accept-new", local, f"{host}:{remote}"],
        capture_output=True,
        text=True,
        timeout=180,
    )
    print("scp", remote_name, r.returncode, r.stderr.strip())
    if r.returncode != 0:
        raise SystemExit(1)

r1 = subprocess.run(
    ["scp", "-i", key, "-o", "StrictHostKeyChecking=accept-new", script_path, f"{host}:{remote_up}/deploy_footer_dynamic.sh"],
    capture_output=True,
    text=True,
    timeout=180,
)
print("scp deploy script", r1.returncode, r1.stderr.strip())
if r1.returncode != 0:
    raise SystemExit(1)

r2 = subprocess.run(
    ["ssh", "-i", key, "-o", "StrictHostKeyChecking=accept-new", host, f"bash {remote_up}/deploy_footer_dynamic.sh"],
    capture_output=True,
    text=True,
    timeout=180,
)
print(r2.stdout)
print(r2.stderr)
print("exit", r2.returncode)
raise SystemExit(r2.returncode)
