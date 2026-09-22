import subprocess, os

script = r"""#!/bin/bash
HOME="/home/sites/41b/b/ba690bc503"
PUB="$HOME/public_html"
APP="$HOME/MOA_lARAVEL"

chmod 755 "$HOME"
chmod 755 "$PUB"
find "$PUB" -type d -exec chmod 755 {} \;
find "$PUB" -type f -exec chmod 644 {} \;
chmod -R u+rwX "$APP/storage" "$APP/bootstrap/cache"

cat > "$HOME/.htaccess" <<'HTA'
#+PHPVersion
#=php82
AddHandler x-httpd-php82 .php
#-PHPVersion
HTA

cat > "$PUB/.htaccess" <<'HTA'
<IfModule mod_rewrite.c>
    Options +FollowSymLinks -MultiViews
    RewriteEngine On
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteRule ^ index.php [L]
</IfModule>
DirectoryIndex index.php
HTA

echo MOA-SITE-OK > "$PUB/ping.txt"
chmod 644 "$PUB/ping.txt"

cd "$APP"
php82 artisan config:cache 2>/dev/null
echo DONE
ls -ld "$HOME" "$PUB"
"""

p = os.path.join(os.path.dirname(__file__), "_fix403b.sh")
open(p, "w", newline="\n").write(script)
k = os.path.expanduser("~/.ssh/id_ed25519")
h = "phi-ltbi-aiia.in@ssh.gb.stackcp.com"
subprocess.run(["scp", "-i", k, p, f"{h}:/home/sites/41b/b/ba690bc503/moa_deploy_upload/fix403b.sh"], check=True)
r = subprocess.run(["ssh", "-i", k, h, "bash /home/sites/41b/b/ba690bc503/moa_deploy_upload/fix403b.sh"], capture_output=True, text=True)
print(r.stdout)
