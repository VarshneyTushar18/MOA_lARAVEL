import subprocess, os
script = r"""#!/bin/bash
HOME_DIR="/home/sites/41b/b/ba690bc503"
cat > "$HOME_DIR/.htaccess" <<'HTA'
#+PHPVersion
#=php81
AddHandler x-httpd-php81 .php
#-PHPVersion
HTA
[ -f "$HOME_DIR/public_html/.user.ini" ] && mv "$HOME_DIR/public_html/.user.ini" "$HOME_DIR/public_html/.user.ini.bak"
echo ping-ok > "$HOME_DIR/public_html/ping.txt"
chmod 644 "$HOME_DIR/public_html/ping.txt"
cat "$HOME_DIR/.htaccess"
"""
p = os.path.join(os.path.dirname(__file__), "_ht81.sh")
open(p, "w", newline="\n").write(script)
k = os.path.expanduser("~/.ssh/id_ed25519")
h = "phi-ltbi-aiia.in@ssh.gb.stackcp.com"
subprocess.run(["scp", "-i", k, p, f"{h}:/home/sites/41b/b/ba690bc503/moa_deploy_upload/ht81.sh"], check=True)
r = subprocess.run(["ssh", "-i", k, h, "bash /home/sites/41b/b/ba690bc503/moa_deploy_upload/ht81.sh"], capture_output=True, text=True)
print(r.stdout, r.stderr)
