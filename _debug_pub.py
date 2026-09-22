import subprocess
import os

script = r"""#!/bin/bash
HOME_DIR="/home/sites/41b/b/ba690bc503"
PUB="$HOME_DIR/public_html"

cat > "$HOME_DIR/.htaccess" <<'HTA'
#+PHPVersion
#=php82
AddHandler x-httpd-php82 .php
#-PHPVersion
HTA

echo 'static-ok' > "$PUB/static.txt"
echo '<?php echo "php-ok-" . PHP_VERSION;' > "$PUB/ok.php"

# enable display errors temporarily
cat > "$PUB/debug.php" <<'PHP'
<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
echo "PHP " . PHP_VERSION . "\n";
require __DIR__ . '/../MOA_lARAVEL/vendor/autoload.php';
echo "autoload ok\n";
$app = require __DIR__ . '/../MOA_lARAVEL/bootstrap/app.php';
echo "bootstrap ok\n";
PHP

ls -la "$PUB/static.txt" "$PUB/ok.php" "$PUB/debug.php"
cat "$HOME_DIR/.htaccess"
echo DONE
"""

path = os.path.join(os.path.dirname(__file__), "_debug_pub.sh")
with open(path, "w", encoding="utf-8", newline="\n") as f:
    f.write(script)
key = os.path.expanduser("~/.ssh/id_ed25519")
host = "phi-ltbi-aiia.in@ssh.gb.stackcp.com"
remote = "/home/sites/41b/b/ba690bc503/moa_deploy_upload/debug_pub.sh"
subprocess.run(["scp", "-i", key, path, f"{host}:{remote}"], check=True)
r = subprocess.run(["ssh", "-i", key, host, f"bash {remote}"], capture_output=True, text=True, timeout=60)
print(r.stdout)
