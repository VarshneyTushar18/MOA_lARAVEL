import subprocess
import os

key = os.path.expanduser("~/.ssh/id_ed25519")
host = "phi-ltbi-aiia.in@ssh.gb.stackcp.com"
base = os.path.dirname(__file__)
remote_up = "/home/sites/41b/b/ba690bc503/moa_deploy_upload"
app = "/home/sites/41b/b/ba690bc503/MOA_lARAVEL"

uploads = [
    ("app/Models/SiteSetting.php", "SiteSetting.php"),
    ("app/Services/PatientCornerAuthService.php", "PatientCornerAuthService.php"),
    ("app/Http/Middleware/EnsurePatientCornerAuthenticated.php", "EnsurePatientCornerAuthenticated.php"),
    ("app/Http/Controllers/PatientCornerAuthController.php", "PatientCornerAuthController.php"),
    ("app/Http/Controllers/Console/PatientCornerAccessController.php", "PatientCornerAccessController.php"),
    ("app/Http/Kernel.php", "Kernel.php"),
    ("routes/web.php", "web.php"),
    ("database/migrations/2026_09_22_160000_create_site_settings_table.php", "2026_09_22_160000_create_site_settings_table.php"),
    ("resources/views/pages/patient_corner_login.blade.php", "patient_corner_login.blade.php"),
    ("resources/views/pages/patient_corner.blade.php", "patient_corner.blade.php"),
    ("resources/views/console/patient_corner_access/edit.blade.php", "patient_corner_access_edit.blade.php"),
    ("resources/views/console/dashboard.blade.php", "dashboard.blade.php"),
]

script = f"""#!/bin/bash
set -e
APP="{app}"
UP="{remote_up}"
mkdir -p "$APP/app/Models" "$APP/app/Services" "$APP/app/Http/Middleware" "$APP/app/Http/Controllers" "$APP/app/Http/Controllers/Console" "$APP/database/migrations" "$APP/resources/views/pages" "$APP/resources/views/console/patient_corner_access"
cp "$UP/SiteSetting.php" "$APP/app/Models/SiteSetting.php"
cp "$UP/PatientCornerAuthService.php" "$APP/app/Services/PatientCornerAuthService.php"
cp "$UP/EnsurePatientCornerAuthenticated.php" "$APP/app/Http/Middleware/EnsurePatientCornerAuthenticated.php"
cp "$UP/PatientCornerAuthController.php" "$APP/app/Http/Controllers/PatientCornerAuthController.php"
cp "$UP/PatientCornerAccessController.php" "$APP/app/Http/Controllers/Console/PatientCornerAccessController.php"
cp "$UP/Kernel.php" "$APP/app/Http/Kernel.php"
cp "$UP/web.php" "$APP/routes/web.php"
cp "$UP/2026_09_22_160000_create_site_settings_table.php" "$APP/database/migrations/2026_09_22_160000_create_site_settings_table.php"
cp "$UP/patient_corner_login.blade.php" "$APP/resources/views/pages/patient_corner_login.blade.php"
cp "$UP/patient_corner.blade.php" "$APP/resources/views/pages/patient_corner.blade.php"
cp "$UP/patient_corner_access_edit.blade.php" "$APP/resources/views/console/patient_corner_access/edit.blade.php"
cp "$UP/dashboard.blade.php" "$APP/resources/views/console/dashboard.blade.php"
cd "$APP"
php82 artisan migrate --force
php82 artisan view:clear
php82 artisan route:clear
php82 artisan config:clear
php82 -r "if (function_exists('opcache_reset')) opcache_reset();"
echo DONE
"""

script_path = os.path.join(base, "_deploy_patient_corner_auth.sh")
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
    ["scp", "-i", key, "-o", "StrictHostKeyChecking=accept-new", script_path, f"{host}:{remote_up}/deploy_patient_corner_auth.sh"],
    check=True,
)
r2 = subprocess.run(
    ["ssh", "-i", key, host, f"bash {remote_up}/deploy_patient_corner_auth.sh"],
    capture_output=True,
    text=True,
    timeout=180,
)
print(r2.stdout)
print(r2.stderr)
if r2.returncode != 0:
    raise SystemExit(r2.returncode)
