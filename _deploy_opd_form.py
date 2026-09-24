import subprocess
import os

key = os.path.expanduser("~/.ssh/id_ed25519")
host = "phi-ltbi-aiia.in@ssh.gb.stackcp.com"
base = os.path.dirname(__file__)
remote_up = "/home/sites/41b/b/ba690bc503/moa_deploy_upload"
app = "/home/sites/41b/b/ba690bc503/MOA_lARAVEL"
public_html = "/home/sites/41b/b/ba690bc503/public_html"

uploads = [
    ("app/Support/OpdPatientFields.php", "OpdPatientFields.php"),
    ("app/Exports/PatientsExport.php", "PatientsExport.php"),
    ("app/Imports/PatientsImport.php", "PatientsImport.php"),
    ("resources/views/pages/patient_corner.blade.php", "patient_corner.blade.php"),
    ("resources/views/patient_console/show.blade.php", "patient_console_show.blade.php"),
    ("public/assets/css/main.css", "main.css"),
]

script = f"""#!/bin/bash
set -e
APP="{app}"
WEB="{public_html}"
UP="{remote_up}"
mkdir -p "$APP/app/Support" "$APP/app/Exports" "$APP/app/Imports" "$APP/resources/views/pages" "$APP/resources/views/patient_console"
cp "$UP/OpdPatientFields.php" "$APP/app/Support/OpdPatientFields.php"
cp "$UP/PatientsExport.php" "$APP/app/Exports/PatientsExport.php"
cp "$UP/PatientsImport.php" "$APP/app/Imports/PatientsImport.php"
cp "$UP/patient_corner.blade.php" "$APP/resources/views/pages/patient_corner.blade.php"
cp "$UP/patient_console_show.blade.php" "$APP/resources/views/patient_console/show.blade.php"
cp "$UP/main.css" "$APP/public/assets/css/main.css"
cp "$UP/main.css" "$WEB/assets/css/main.css"
cd "$APP"
php82 artisan view:clear
php82 -r "if (function_exists('opcache_reset')) opcache_reset();"
echo DONE
"""

script_path = os.path.join(base, "_deploy_opd_form.sh")
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
    ["scp", "-i", key, "-o", "StrictHostKeyChecking=accept-new", script_path, f"{host}:{remote_up}/deploy_opd_form.sh"],
    check=True,
)
r2 = subprocess.run(
    ["ssh", "-i", key, host, f"bash {remote_up}/deploy_opd_form.sh"],
    capture_output=True,
    text=True,
    timeout=180,
)
print(r2.stdout)
print(r2.stderr)
if r2.returncode != 0:
    raise SystemExit(r2.returncode)
