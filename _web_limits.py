import subprocess
import os

script = r"""#!/bin/bash
APP="/home/sites/41b/b/ba690bc503/MOA_lARAVEL"
cd "$APP"
php82 artisan tinker --execute="echo 'limit=' . \App\Support\UploadLimits::effectiveMaxLabel() . PHP_EOL;"
echo "=== web php test file ==="
cat > /home/sites/41b/b/ba690bc503/public_html/_upload_limits.php <<'PHP'
<?php
header('Content-Type: text/plain');
echo 'upload_max_filesize=' . ini_get('upload_max_filesize') . "\n";
echo 'post_max_size=' . ini_get('post_max_size') . "\n";
echo 'memory_limit=' . ini_get('memory_limit') . "\n";
PHP
"""

base = os.path.dirname(__file__)
path = os.path.join(base, "_web_limits.sh")
with open(path, "w", encoding="utf-8", newline="\n") as f:
    f.write(script)

key = os.path.expanduser("~/.ssh/id_ed25519")
host = "phi-ltbi-aiia.in@ssh.gb.stackcp.com"
remote = "/home/sites/41b/b/ba690bc503/moa_deploy_upload/web_limits.sh"
subprocess.run(["scp", "-i", key, path, f"{host}:{remote}"], check=True)
subprocess.run(["ssh", "-i", key, host, f"bash {remote}"], check=True)
r = subprocess.run(["curl.exe", "-s", "https://phi-ltbi-aiia-in.stackstaging.com/_upload_limits.php"], capture_output=True, text=True)
print(r.stdout)
# cleanup
subprocess.run(["ssh", "-i", key, host, "rm -f /home/sites/41b/b/ba690bc503/public_html/_upload_limits.php"], check=True)
