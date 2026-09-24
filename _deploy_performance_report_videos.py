import os
import subprocess
import time

KEY = os.path.expanduser("~/.ssh/id_ed25519")
HOST = "phi-ltbi-aiia.in@ssh.gb.stackcp.com"
REMOTE_UP = "/home/sites/41b/b/ba690bc503/moa_deploy_upload"
APP = "/home/sites/41b/b/ba690bc503/MOA_lARAVEL"

base = os.path.dirname(__file__)
local = os.path.join(base, "resources", "views", "pages", "performance_report.blade.php")

for attempt in range(1, 6):
    r = subprocess.run(
        ["scp", "-i", KEY, local, f"{HOST}:{REMOTE_UP}/performance_report.blade.php"],
        capture_output=True,
        text=True,
        timeout=180,
    )
    if r.returncode == 0:
        break
    time.sleep(10)

if r.returncode != 0:
    raise SystemExit(r.stderr)

r2 = subprocess.run(
    ["ssh", "-i", KEY, HOST, f"cp {REMOTE_UP}/performance_report.blade.php {APP}/resources/views/pages/performance_report.blade.php && cd {APP} && php82 artisan view:clear && php82 artisan view:cache && echo DONE"],
    capture_output=True,
    text=True,
    timeout=120000,
)
print(r2.stdout)
print(r2.stderr)
if r2.returncode != 0:
    raise SystemExit(r2.returncode)
