import subprocess
import os

key = os.path.expanduser("~/.ssh/id_ed25519")
host = "phi-ltbi-aiia.in@ssh.gb.stackcp.com"
base = os.path.dirname(__file__)
remote_up = "/home/sites/41b/b/ba690bc503/moa_deploy_upload"
pub = "/home/sites/41b/b/ba690bc503/public_html"

script = """#!/bin/bash
set -e
PUB="/home/sites/41b/b/ba690bc503/public_html"
APP="/home/sites/41b/b/ba690bc503/MOA_lARAVEL"
UP="/home/sites/41b/b/ba690bc503/moa_deploy_upload"
mkdir -p "$PUB/assets/images" "$APP/public/assets/images"
cp "$UP/ministry-ayush-logo.png" "$PUB/assets/images/ministry-ayush-logo.png"
cp "$UP/ministry-ayush-logo.png" "$APP/public/assets/images/ministry-ayush-logo.png"
cp "$UP/Main-logo.png" "$PUB/assets/images/Main-logo.png"
cp "$UP/Main-logo.png" "$APP/public/assets/images/Main-logo.png"
echo DONE
"""

script_path = os.path.join(base, "_deploy_ministry_logo.sh")
with open(script_path, "w", encoding="utf-8", newline="\n") as f:
    f.write(script)

for local_name in ["ministry-ayush-logo.png", "Main-logo.png"]:
    local = os.path.join(base, "public", "assets", "images", local_name)
    r = subprocess.run(
        ["scp", "-i", key, "-o", "StrictHostKeyChecking=accept-new", local, f"{host}:{remote_up}/{local_name}"],
        capture_output=True,
        text=True,
        timeout=180,
    )
    print("scp", local_name, r.returncode, r.stderr.strip())
    if r.returncode != 0:
        raise SystemExit(1)

subprocess.run(
    ["scp", "-i", key, "-o", "StrictHostKeyChecking=accept-new", script_path, f"{host}:{remote_up}/deploy_ministry_logo.sh"],
    check=True,
)
r2 = subprocess.run(
    ["ssh", "-i", key, "-o", "StrictHostKeyChecking=accept-new", host, f"bash {remote_up}/deploy_ministry_logo.sh"],
    capture_output=True,
    text=True,
    timeout=120,
)
print(r2.stdout)
print(r2.stderr)
print("exit", r2.returncode)
