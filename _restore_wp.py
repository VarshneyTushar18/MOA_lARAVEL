import subprocess
import os

script = r"""#!/bin/bash
set -e
HOME_DIR="/home/sites/41b/b/ba690bc503"
# backup current moa public_html
if [ -d "$HOME_DIR/public_html_moa_backup" ]; then rm -rf "$HOME_DIR/public_html_moa_backup"; fi
mv "$HOME_DIR/public_html" "$HOME_DIR/public_html_moa_backup"
cp -a "$HOME_DIR/public_html_wp_backup" "$HOME_DIR/public_html"
cat > "$HOME_DIR/.htaccess" <<'HTA'
#+PHPVersion
#=php74
AddHandler x-httpd-php74 .php
#-PHPVersion
HTA
echo restored wordpress public_html
ls "$HOME_DIR/public_html/index.php"
"""

path = os.path.join(os.path.dirname(__file__), "_restore_wp.sh")
with open(path, "w", encoding="utf-8", newline="\n") as f:
    f.write(script)
key = os.path.expanduser("~/.ssh/id_ed25519")
host = "phi-ltbi-aiia.in@ssh.gb.stackcp.com"
remote = "/home/sites/41b/b/ba690bc503/moa_deploy_upload/restore_wp.sh"
subprocess.run(["scp", "-i", key, path, f"{host}:{remote}"], check=True)
r = subprocess.run(["ssh", "-i", key, host, f"bash {remote}"], capture_output=True, text=True, timeout=120)
print(r.stdout)
print(r.stderr)
