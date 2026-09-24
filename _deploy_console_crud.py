import subprocess
import os

key = os.path.expanduser("~/.ssh/id_ed25519")
host = "phi-ltbi-aiia.in@ssh.gb.stackcp.com"
base = os.path.dirname(__file__)
remote_up = "/home/sites/41b/b/ba690bc503/moa_deploy_upload"
app = "/home/sites/41b/b/ba690bc503/MOA_lARAVEL"

uploads = [
    ("routes/web.php", "web.php"),
    ("app/Http/Controllers/ContactController.php", "ContactController.php"),
    ("app/Http/Controllers/PatientController.php", "PatientController.php"),
    ("app/Http/Controllers/CureController.php", "CureController.php"),
    ("app/Http/Controllers/ResearchPatientController.php", "ResearchPatientController.php"),
    ("app/Http/Controllers/IdCardController.php", "IdCardController.php"),
    ("app/Http/Controllers/Console/SurveyResponseController.php", "ConsoleSurveyResponseController.php"),
    ("resources/views/partials/console_record_actions.blade.php", "console_record_actions.blade.php"),
    ("resources/views/contacts_console/list.blade.php", "contacts_list.blade.php"),
    ("resources/views/contacts_console/show.blade.php", "contacts_show.blade.php"),
    ("resources/views/contacts_console/edit.blade.php", "contacts_edit.blade.php"),
    ("resources/views/patient_console/list.blade.php", "patient_list.blade.php"),
    ("resources/views/patient_console/show.blade.php", "patient_show.blade.php"),
    ("resources/views/patient_console/edit.blade.php", "patient_edit.blade.php"),
    ("resources/views/cure_console/list.blade.php", "cure_list.blade.php"),
    ("resources/views/cure_console/show.blade.php", "cure_show.blade.php"),
    ("resources/views/cure_console/edit.blade.php", "cure_edit.blade.php"),
    ("resources/views/research_console/list.blade.php", "research_list.blade.php"),
    ("resources/views/research_console/show.blade.php", "research_show.blade.php"),
    ("resources/views/research_console/edit.blade.php", "research_edit.blade.php"),
    ("resources/views/idcard_console/list.blade.php", "idcard_list.blade.php"),
    ("resources/views/idcard_console/show.blade.php", "idcard_show.blade.php"),
    ("resources/views/idcard_console/edit.blade.php", "idcard_edit.blade.php"),
    ("resources/views/console/survey_responses/index.blade.php", "survey_index.blade.php"),
    ("resources/views/console/survey_responses/show.blade.php", "survey_show.blade.php"),
    ("resources/views/console/survey_responses/edit.blade.php", "survey_edit.blade.php"),
]

script = f"""#!/bin/bash
set -e
APP="{app}"
UP="{remote_up}"
mkdir -p "$APP/routes" "$APP/app/Http/Controllers" "$APP/app/Http/Controllers/Console" "$APP/resources/views/partials" "$APP/resources/views/contacts_console" "$APP/resources/views/patient_console" "$APP/resources/views/cure_console" "$APP/resources/views/research_console" "$APP/resources/views/idcard_console" "$APP/resources/views/console/survey_responses"
cp "$UP/web.php" "$APP/routes/web.php"
cp "$UP/ContactController.php" "$APP/app/Http/Controllers/ContactController.php"
cp "$UP/PatientController.php" "$APP/app/Http/Controllers/PatientController.php"
cp "$UP/CureController.php" "$APP/app/Http/Controllers/CureController.php"
cp "$UP/ResearchPatientController.php" "$APP/app/Http/Controllers/ResearchPatientController.php"
cp "$UP/IdCardController.php" "$APP/app/Http/Controllers/IdCardController.php"
cp "$UP/ConsoleSurveyResponseController.php" "$APP/app/Http/Controllers/Console/SurveyResponseController.php"
cp "$UP/console_record_actions.blade.php" "$APP/resources/views/partials/console_record_actions.blade.php"
cp "$UP/contacts_list.blade.php" "$APP/resources/views/contacts_console/list.blade.php"
cp "$UP/contacts_show.blade.php" "$APP/resources/views/contacts_console/show.blade.php"
cp "$UP/contacts_edit.blade.php" "$APP/resources/views/contacts_console/edit.blade.php"
cp "$UP/patient_list.blade.php" "$APP/resources/views/patient_console/list.blade.php"
cp "$UP/patient_show.blade.php" "$APP/resources/views/patient_console/show.blade.php"
cp "$UP/patient_edit.blade.php" "$APP/resources/views/patient_console/edit.blade.php"
cp "$UP/cure_list.blade.php" "$APP/resources/views/cure_console/list.blade.php"
cp "$UP/cure_show.blade.php" "$APP/resources/views/cure_console/show.blade.php"
cp "$UP/cure_edit.blade.php" "$APP/resources/views/cure_console/edit.blade.php"
cp "$UP/research_list.blade.php" "$APP/resources/views/research_console/list.blade.php"
cp "$UP/research_show.blade.php" "$APP/resources/views/research_console/show.blade.php"
cp "$UP/research_edit.blade.php" "$APP/resources/views/research_console/edit.blade.php"
cp "$UP/idcard_list.blade.php" "$APP/resources/views/idcard_console/list.blade.php"
cp "$UP/idcard_show.blade.php" "$APP/resources/views/idcard_console/show.blade.php"
cp "$UP/idcard_edit.blade.php" "$APP/resources/views/idcard_console/edit.blade.php"
cp "$UP/survey_index.blade.php" "$APP/resources/views/console/survey_responses/index.blade.php"
cp "$UP/survey_show.blade.php" "$APP/resources/views/console/survey_responses/show.blade.php"
cp "$UP/survey_edit.blade.php" "$APP/resources/views/console/survey_responses/edit.blade.php"
cd "$APP"
php82 artisan route:clear
php82 artisan view:clear
php82 -r "if (function_exists('opcache_reset')) opcache_reset();"
echo DONE
"""

script_path = os.path.join(base, "_deploy_console_crud.sh")
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
    ["scp", "-i", key, "-o", "StrictHostKeyChecking=accept-new", script_path, f"{host}:{remote_up}/deploy_console_crud.sh"],
    check=True,
)
r2 = subprocess.run(
    ["ssh", "-i", key, host, f"bash {remote_up}/deploy_console_crud.sh"],
    capture_output=True,
    text=True,
    timeout=180000,
)
print(r2.stdout)
print(r2.stderr)
if r2.returncode != 0:
    raise SystemExit(r2.returncode)
