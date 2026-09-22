import subprocess
import os

script = r"""#!/bin/bash
HOME_DIR="/home/sites/41b/b/ba690bc503"
for v in php81 php82 php83 php80 php74; do
  cat > "$HOME_DIR/.htaccess" <<HTA
#+PHPVersion
#=$v
AddHandler x-httpd-$v .php
#-PHPVersion
HTA
  sleep 2
  code=$(curl -s -o /tmp/out.txt -w '%{http_code}' "https://phi-ltbi-aiia.in/ok.php")
  body=$(head -c 80 /tmp/out.txt)
  echo "$v $code $body"
  if [ "$code" = "200" ]; then
    echo WORKING=$v
    break
  fi
done
"""
path = os.path.join(os.path.dirname(__file__), "_try_php_remote.sh")
with open(path, "w", encoding="utf-8", newline="\n") as f:
    f.write(script)
key = os.path.expanduser("~/.ssh/id_ed25519")
host = "phi-ltbi-aiia.in@ssh.gb.stackcp.com"
remote = "/home/sites/41b/b/ba690bc503/moa_deploy_upload/try_php.sh"
subprocess.run(["scp", "-i", key, path, f"{host}:{remote}"], check=True)
r = subprocess.run(["ssh", "-i", key, host, f"bash {remote}"], capture_output=True, text=True, timeout=120)
print(r.stdout)
print(r.stderr)
