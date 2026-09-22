#!/bin/bash
set -e
HOME_DIR="/home/sites/41b/b/ba690bc503"
cat > "$HOME_DIR/.htaccess" <<'HTA'
#+PHPVersion
#=php82
AddHandler x-httpd-php82 .php
#-PHPVersion
HTA

cat > "$HOME_DIR/public_html/.htaccess" <<'HTA'
<IfModule mod_rewrite.c>
    <IfModule mod_negotiation.c>
        Options -MultiViews -Indexes
    </IfModule>
    RewriteEngine On
    RewriteCond %{HTTP:Authorization} .
    RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_URI} (.+)/$
    RewriteRule ^ %1 [L,R=301]
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteRule ^ index.php [L]
</IfModule>
HTA

chmod -R u+rwX "$HOME_DIR/MOA_lARAVEL/storage" "$HOME_DIR/MOA_lARAVEL/bootstrap/cache"
cd "$HOME_DIR/MOA_lARAVEL"
php82 artisan config:clear
php82 artisan config:cache
curl -s -o /dev/null -w "home=%{http_code}\n" "https://phi-ltbi-aiia.in/"
curl -s -o /dev/null -w "login=%{http_code}\n" "https://phi-ltbi-aiia.in/console/login"
echo DONE
