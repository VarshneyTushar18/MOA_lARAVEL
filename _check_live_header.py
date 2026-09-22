import subprocess
import os

script = r"""#!/bin/bash
APP="/home/sites/41b/b/ba690bc503/MOA_lARAVEL"
PUB="/home/sites/41b/b/ba690bc503/public_html"
echo "=== frontend.blade.php on server ==="
grep -n "aiia-header\|ministry-ayush" "$APP/resources/views/layout/frontend.blade.php" | head -5
echo "=== image files ==="
ls -la "$PUB/assets/images/aiia-header"* 2>&1
echo "=== curl local index snippet ==="
grep -o 'assets/images/[^"]*aiia[^"]*' "$PUB/index.php" 2>/dev/null || true
php82 -r 'echo file_exists("'$PUB'/assets/images/aiia-header-brand.png") ? "brand exists\n" : "brand MISSING\n";'
php82 -r 'echo file_exists("'$PUB'/assets/images/aiia-header-logo.png") ? "logo exists\n" : "logo MISSING\n";'
"""

base = os.path.dirname(os.path.abspath(__file__))
path = os.path.join(base, "_check_live_header.sh")
with open(path, "w", encoding="utf-8", newline="\n") as f:
    f.write(script)

key = os.path.expanduser("~/.ssh/id_ed25519")
host = "phi-ltbi-aiia.in@ssh.gb.stackcp.com"
remote = "/home/sites/41b/b/ba690bc503/moa_deploy_upload/check_live_header.sh"
subprocess.run(["scp", "-i", key, "-o", "StrictHostKeyChecking=accept-new", path, f"{host}:{remote}"], check=True)
r = subprocess.run(["ssh", "-i", key, host, f"bash {remote}"], capture_output=True, text=True, timeout=120)
print(r.stdout)
print(r.stderr)
