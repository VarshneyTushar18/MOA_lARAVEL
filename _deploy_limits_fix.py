import os
import subprocess

KEY = os.path.expanduser("~/.ssh/id_ed25519")
HOST = "phi-ltbi-aiia.in@ssh.gb.stackcp.com"
REMOTE_UP = "/home/sites/41b/b/ba690bc503/moa_deploy_upload"
APP = "/home/sites/41b/b/ba690bc503/MOA_lARAVEL"
PUBLIC_HTML = "/home/sites/41b/b/ba690bc503/public_html"

USER_INI = """; PHP upload limits — keep in sync with StackCP panel (12800M = 12.5 GB)
upload_max_filesize = 12800M
post_max_size = 12800M
max_file_uploads = 1000
max_input_vars = 10000
memory_limit = 2048M
max_execution_time = 0
max_input_time = 0
"""

base = os.path.dirname(__file__)
ini_path = os.path.join(base, "public", ".user.ini")
with open(ini_path, "w", encoding="utf-8", newline="\n") as f:
    f.write(USER_INI)

script = f"""#!/bin/bash
set -e
cp {REMOTE_UP}/.user.ini {APP}/public/.user.ini
cp {REMOTE_UP}/.user.ini {PUBLIC_HTML}/.user.ini
# StackCP merges ;+StackCP block — append only our overrides after EOF marker
if ! grep -q ';+Unmarked' {PUBLIC_HTML}/.user.ini 2>/dev/null; then
  cat >> {PUBLIC_HTML}/.user.ini <<'INI'

;+Unmarked
upload_max_filesize = 12800M
post_max_size = 12800M
max_file_uploads = 1000
max_input_vars = 10000
memory_limit = 2048M
max_execution_time = 0
max_input_time = 0
;-Unmarked
INI
fi
cd {APP}
sed -i 's/^UPLOAD_MAX_VIDEO_MB=.*/UPLOAD_MAX_VIDEO_MB=0/' .env || true
grep -q '^UPLOAD_MAX_VIDEO_MB=' .env || echo 'UPLOAD_MAX_VIDEO_MB=0' >> .env
php82 artisan config:clear
php82 artisan view:clear
php82 artisan view:cache
php82 -r "if (function_exists('opcache_reset')) opcache_reset();"
echo DONE
"""

script_path = os.path.join(base, "_deploy_limits_fix.sh")
with open(script_path, "w", encoding="utf-8", newline="\n") as f:
    f.write(script)

for local, remote in [
    (ini_path, ".user.ini"),
    (os.path.join(base, "_check_web_limits.php"), "check_web_limits.php"),
]:
    r = subprocess.run(
        ["scp", "-i", KEY, local, f"{HOST}:{REMOTE_UP}/{os.path.basename(remote) if remote.endswith('.php') else remote}"],
        capture_output=True,
        text=True,
        timeout=180,
    )
    print("scp", remote, r.returncode, r.stderr.strip() or "ok")

subprocess.run(["scp", "-i", KEY, script_path, f"{HOST}:{REMOTE_UP}/deploy_limits_fix.sh"], check=True)
r2 = subprocess.run(
    ["ssh", "-i", KEY, HOST, f"bash {REMOTE_UP}/deploy_limits_fix.sh"],
    capture_output=True,
    text=True,
    timeout=120000,
)
print(r2.stdout)
print(r2.stderr)

# copy check script to public and curl
subprocess.run(
    ["ssh", "-i", KEY, HOST, f"cp {REMOTE_UP}/check_web_limits.php {PUBLIC_HTML}/_check_web_limits.php"],
    check=True,
)
r3 = subprocess.run(
    ["curl.exe", "-s", "https://phi-ltbi-aiia-in.stackstaging.com/_check_web_limits.php"],
    capture_output=True,
    text=True,
    timeout=60,
)
print("=== WEB LIMITS ===")
print(r3.stdout)
subprocess.run(["ssh", "-i", KEY, HOST, f"rm -f {PUBLIC_HTML}/_check_web_limits.php"], check=True)

if r2.returncode != 0:
    raise SystemExit(r2.returncode)
