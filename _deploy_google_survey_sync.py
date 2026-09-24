import subprocess
import os

key = os.path.expanduser("~/.ssh/id_ed25519")
host = "phi-ltbi-aiia.in@ssh.gb.stackcp.com"
base = os.path.dirname(__file__)
remote_up = "/home/sites/41b/b/ba690bc503/moa_deploy_upload"
app = "/home/sites/41b/b/ba690bc503/MOA_lARAVEL"

uploads = [
    ("app/Http/Controllers/SurveyResponseController.php", "SurveyResponseController.php"),
    ("app/Services/GoogleSurveyFormSyncService.php", "GoogleSurveyFormSyncService.php"),
    ("app/Exports/SurveyResponsesExport.php", "SurveyResponsesExport.php"),
    ("resources/views/pages/screening_performa.blade.php", "screening_performa.blade.php"),
    ("resources/views/console/survey_responses/show.blade.php", "survey_responses_show.blade.php"),
]

script = f"""#!/bin/bash
set -e
APP="{app}"
UP="{remote_up}"
mkdir -p "$APP/app/Http/Controllers" "$APP/app/Services" "$APP/app/Exports" "$APP/resources/views/pages" "$APP/resources/views/console/survey_responses"
cp "$UP/SurveyResponseController.php" "$APP/app/Http/Controllers/SurveyResponseController.php"
cp "$UP/GoogleSurveyFormSyncService.php" "$APP/app/Services/GoogleSurveyFormSyncService.php"
cp "$UP/SurveyResponsesExport.php" "$APP/app/Exports/SurveyResponsesExport.php"
cp "$UP/screening_performa.blade.php" "$APP/resources/views/pages/screening_performa.blade.php"
cp "$UP/survey_responses_show.blade.php" "$APP/resources/views/console/survey_responses/show.blade.php"
cd "$APP"
php82 artisan view:clear
php82 artisan config:clear
php82 -r "if (function_exists('opcache_reset')) opcache_reset();"
echo DONE
"""

script_path = os.path.join(base, "_deploy_google_survey_sync.sh")
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
    ["scp", "-i", key, "-o", "StrictHostKeyChecking=accept-new", script_path, f"{host}:{remote_up}/deploy_google_survey_sync.sh"],
    check=True,
)
r2 = subprocess.run(
    ["ssh", "-i", key, host, f"bash {remote_up}/deploy_google_survey_sync.sh"],
    capture_output=True,
    text=True,
    timeout=180,
)
print(r2.stdout)
print(r2.stderr)
