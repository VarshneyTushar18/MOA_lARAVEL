import subprocess
import os

script = r"""#!/bin/bash
APP="/home/sites/41b/b/ba690bc503/MOA_lARAVEL"
cd "$APP"
echo "=== log size ==="
wc -l storage/logs/laravel.log 2>/dev/null || echo no log
echo "=== last 3 ERROR blocks ==="
grep -a -n "local.ERROR" storage/logs/laravel.log | tail -3
echo "=== tail context ==="
tail -80 storage/logs/laravel.log 2>/dev/null
"""

base = os.path.dirname(__file__)
path = os.path.join(base, "_read_log.sh")
with open(path, "w", encoding="utf-8", newline="\n") as f:
    f.write(script)

key = os.path.expanduser("~/.ssh/id_ed25519")
host = "phi-ltbi-aiia.in@ssh.gb.stackcp.com"
remote = "/home/sites/41b/b/ba690bc503/moa_deploy_upload/read_log.sh"
subprocess.run(["scp", "-i", key, path, f"{host}:{remote}"], check=True)
r = subprocess.run(["ssh", "-i", key, host, f"bash {remote}"], capture_output=True, text=True, timeout=120)
print(r.stdout[-8000:] if len(r.stdout) > 8000 else r.stdout)
print(r.stderr)
