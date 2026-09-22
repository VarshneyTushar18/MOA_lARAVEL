import subprocess
import os

script = r"""#!/bin/bash
APP="/home/sites/41b/b/ba690bc503/MOA_lARAVEL"
cd "$APP"
echo "=== marqueeValidationRules on server ==="
grep -n "marqueeValidationRules\|marquee_links\.\*\.url\|section_key !== 'home_marquee'" app/Http/Controllers/PageSectionsController.php | head -20
echo "=== edit blade marquee block ==="
grep -n "section_form_marquee\|marquee_links\|home_marquee" resources/views/pages_console/sections/edit.blade.php | head -20
echo "=== setBlockInputsEnabled ==="
grep -n "setBlockInputsEnabled" resources/views/pages_console/sections/edit.blade.php
"""

base = os.path.dirname(os.path.abspath(__file__))
path = os.path.join(base, "_verify_marquee_fix.sh")
with open(path, "w", encoding="utf-8", newline="\n") as f:
    f.write(script)

key = os.path.expanduser("~/.ssh/id_ed25519")
host = "phi-ltbi-aiia.in@ssh.gb.stackcp.com"
remote = "/home/sites/41b/b/ba690bc503/moa_deploy_upload/verify_marquee_fix.sh"
subprocess.run(["scp", "-i", key, "-o", "StrictHostKeyChecking=accept-new", path, f"{host}:{remote}"], check=True)
r = subprocess.run(["ssh", "-i", key, host, f"bash {remote}"], capture_output=True, text=True, timeout=120)
print(r.stdout)
print(r.stderr)
