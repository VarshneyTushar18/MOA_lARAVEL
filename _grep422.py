import subprocess
import os

script = r"""#!/bin/bash
APP="/home/sites/41b/b/ba690bc503/MOA_lARAVEL"
cd "$APP"
grep -a "local.ERROR\|ValidationException\|422" storage/logs/laravel.log 2>/dev/null | tail -15
echo "---"
tail -5 storage/logs/laravel.log 2>/dev/null
"""

base = os.path.dirname(os.path.abspath(__file__))
path = os.path.join(base, "_grep422.sh")
with open(path, "w", encoding="utf-8", newline="\n") as f:
    f.write(script)

key = os.path.expanduser("~/.ssh/id_ed25519")
host = "phi-ltbi-aiia.in@ssh.gb.stackcp.com"
remote = "/home/sites/41b/b/ba690bc503/moa_deploy_upload/grep422.sh"
subprocess.run(["scp", "-i", key, "-o", "StrictHostKeyChecking=accept-new", path, f"{host}:{remote}"], check=True)
r = subprocess.run(["ssh", "-i", key, host, f"bash {remote}"], capture_output=True, text=True, timeout=120)
print(r.stdout)
print(r.stderr)
