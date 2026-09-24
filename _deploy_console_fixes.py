import os
import subprocess
import time

KEY = os.path.expanduser("~/.ssh/id_ed25519")
HOST = "phi-ltbi-aiia.in@ssh.gb.stackcp.com"
REMOTE_UP = "/home/sites/41b/b/ba690bc503/moa_deploy_upload"
APP = "/home/sites/41b/b/ba690bc503/MOA_lARAVEL"
PUBLIC_HTML = "/home/sites/41b/b/ba690bc503/public_html"

USER_INI = """; PHP upload limits — StackCP + app (12800M = 12.5 GB)
;+StackCP
upload_max_filesize = 12800M
post_max_size = 12800M
max_file_uploads = 1000
max_input_vars = 10000
memory_limit = 2048M
max_execution_time = 0
max_input_time = 0
;-StackCP
"""

base = os.path.dirname(__file__)
ini_path = os.path.join(base, "public", ".user.ini")
with open(ini_path, "w", encoding="utf-8", newline="\n") as f:
    f.write(USER_INI)

files = [
    ("public/.user.ini", ".user.ini"),
    ("app/Http/Controllers/PageSectionsController.php", "PageSectionsController.php"),
    ("app/Http/Controllers/ConsoleAccountController.php", "ConsoleAccountController.php"),
    ("app/Providers/AppServiceProvider.php", "AppServiceProvider.php"),
    ("routes/web.php", "web.php"),
    ("public/assets/js/console-upload-progress.js", "console-upload-progress.js"),
    ("resources/views/layout/console.blade.php", "console.blade.php"),
    ("resources/views/console/login.blade.php", "console_login.blade.php"),
    ("resources/views/console/account/password.blade.php", "console_account_password.blade.php"),
    ("resources/views/console/forgot-password.blade.php", "console_forgot_password.blade.php"),
    ("resources/views/console/reset-password.blade.php", "console_reset_password.blade.php"),
    ("_merge_patient_medicine_sections.php", "merge_patient_medicine_sections.php"),
    ("_check_web_limits.php", "check_web_limits.php"),
]

script = f"""#!/bin/bash
set -e
cp {REMOTE_UP}/.user.ini {APP}/public/.user.ini
cp {REMOTE_UP}/.user.ini {PUBLIC_HTML}/.user.ini
cp {REMOTE_UP}/PageSectionsController.php {APP}/app/Http/Controllers/PageSectionsController.php
cp {REMOTE_UP}/ConsoleAccountController.php {APP}/app/Http/Controllers/ConsoleAccountController.php
cp {REMOTE_UP}/AppServiceProvider.php {APP}/app/Providers/AppServiceProvider.php
cp {REMOTE_UP}/web.php {APP}/routes/web.php
cp {REMOTE_UP}/console-upload-progress.js {APP}/public/assets/js/console-upload-progress.js
cp {REMOTE_UP}/console-upload-progress.js {PUBLIC_HTML}/assets/js/console-upload-progress.js
cp {REMOTE_UP}/console.blade.php {APP}/resources/views/layout/console.blade.php
cp {REMOTE_UP}/console_login.blade.php {APP}/resources/views/console/login.blade.php
mkdir -p {APP}/resources/views/console/account
cp {REMOTE_UP}/console_account_password.blade.php {APP}/resources/views/console/account/password.blade.php
cp {REMOTE_UP}/console_forgot_password.blade.php {APP}/resources/views/console/forgot-password.blade.php
cp {REMOTE_UP}/console_reset_password.blade.php {APP}/resources/views/console/reset-password.blade.php
cp {REMOTE_UP}/merge_patient_medicine_sections.php {APP}/_merge_patient_medicine_sections.php
cd {APP}
sed -i 's/^UPLOAD_MAX_VIDEO_MB=.*/UPLOAD_MAX_VIDEO_MB=0/' .env || true
grep -q '^UPLOAD_MAX_VIDEO_MB=' .env || echo 'UPLOAD_MAX_VIDEO_MB=0' >> .env
php82 _merge_patient_medicine_sections.php
php82 artisan config:clear
php82 artisan route:clear
php82 artisan view:clear
php82 artisan view:cache
if php82 -r "if (function_exists('opcache_reset')) opcache_reset();" 2>/dev/null; then echo opcache_cleared; fi
echo DONE
"""

script_path = os.path.join(base, "_deploy_console_fixes.sh")
with open(script_path, "w", encoding="utf-8", newline="\n") as f:
    f.write(script)


def scp_retry(local, remote, retries=5):
    for attempt in range(1, retries + 1):
        r = subprocess.run(
            ["scp", "-i", KEY, "-o", "StrictHostKeyChecking=accept-new", local, f"{HOST}:{REMOTE_UP}/{remote}"],
            capture_output=True,
            text=True,
            timeout=180,
        )
        print("scp", remote, r.returncode, r.stderr.strip() or "ok")
        if r.returncode == 0:
            return True
        time.sleep(10)
    return False


for local_rel, remote in files:
    local = os.path.join(base, local_rel.replace("/", os.sep))
    if not scp_retry(local, remote):
        raise SystemExit(f"SCP failed: {remote}")

if not scp_retry(script_path, "deploy_console_fixes.sh"):
    raise SystemExit("SCP failed: deploy script")

for attempt in range(1, 6):
    r2 = subprocess.run(
        ["ssh", "-i", KEY, HOST, f"bash {REMOTE_UP}/deploy_console_fixes.sh"],
        capture_output=True,
        text=True,
        timeout=180000,
    )
    print(r2.stdout)
    print(r2.stderr)
    if r2.returncode == 0:
        break
    print(f"SSH deploy attempt {attempt} failed, retrying...")
    time.sleep(15)
else:
    raise SystemExit(r2.returncode)

subprocess.run(["ssh", "-i", KEY, HOST, f"cp {REMOTE_UP}/check_web_limits.php {PUBLIC_HTML}/_check_web_limits.php"], check=True)
r3 = subprocess.run(
    ["curl.exe", "-s", "https://phi-ltbi-aiia-in.stackstaging.com/_check_web_limits.php"],
    capture_output=True,
    text=True,
    timeout=60,
)
print("=== WEB LIMITS ===")
print(r3.stdout)
subprocess.run(["ssh", "-i", KEY, HOST, f"rm -f {PUBLIC_HTML}/_check_web_limits.php"], check=True)
