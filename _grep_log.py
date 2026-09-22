import subprocess
import os

script = r"""#!/bin/bash
APP="/home/sites/41b/b/ba690bc503/MOA_lARAVEL"
cd "$APP"
grep -a "PostTooLarge\|Upload too large\|exceeds the PHP\|The image\|images\.\*\|ValidationException\|local.ERROR" storage/logs/laravel.log | tail -20
echo "---"
grep -a -B0 -A2 "local.ERROR" storage/logs/laravel.log | tail -30
"""

base = os.path.dirname(__file__)
path = os.path.join(base, "_grep_log.sh")
with open(path, "w", encoding="utf-8", newline="\n") as f:
    f.write(script)

key = os.path.expanduser("~/.ssh/id_ed25519")
host = "phi-ltbi-aiia.in@ssh.gb.stackcp.com"
remote = "/home/sites/41b/b/ba690bc503/moa_deploy_upload/grep_log.sh"
subprocess.run(["scp", "-i", key, path, f"{host}:{remote}"], check=True)
r = subprocess.run(["ssh", "-i", key, host, f"bash {remote}"], capture_output=True, text=True, timeout=120)
print(r.stdout)
print(r.stderr)
