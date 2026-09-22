import subprocess
import os

script = r"""#!/bin/bash
HOME_DIR="/home/sites/41b/b/ba690bc503"
rm -f "$HOME_DIR/.htaccess"
rm -f "$HOME_DIR/public_html/.htaccess"
echo 'bare-static' > "$HOME_DIR/public_html/bare.txt"
ls -la "$HOME_DIR/.htaccess" "$HOME_DIR/public_html/.htaccess" 2>&1 || true
ls -la "$HOME_DIR/public_html/bare.txt"
echo DONE
"""
path = os.path.join(os.path.dirname(__file__), "_bare.sh")
with open(path, "w", encoding="utf-8", newline="\n") as f:
    f.write(script)
key = os.path.expanduser("~/.ssh/id_ed25519")
host = "phi-ltbi-aiia.in@ssh.gb.stackcp.com"
subprocess.run(["scp", "-i", key, path, f"{host}:/home/sites/41b/b/ba690bc503/moa_deploy_upload/bare.sh"], check=True)
subprocess.run(["ssh", "-i", key, host, "bash /home/sites/41b/b/ba690bc503/moa_deploy_upload/bare.sh"], check=True)
