#!/bin/bash
HOME_DIR="/home/sites/41b/b/ba690bc503"

# Put MOA public_html back
if [ -d "$HOME_DIR/public_html_moa_backup" ]; then
  rm -rf "$HOME_DIR/public_html"
  mv "$HOME_DIR/public_html_moa_backup" "$HOME_DIR/public_html"
fi

echo 'static-ok-v2' > "$HOME_DIR/public_html/static.txt"

# PHP 8.2 in public_html (StackCP style)
cat > "$HOME_DIR/public_html/.htaccess" <<'HTA'
#+PHPVersion
#=php82
AddHandler x-httpd-php82 .php
#-PHPVersion

<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteRule ^ index.php [L]
</IfModule>
HTA

# account-level php82 as well
cat > "$HOME_DIR/.htaccess" <<'HTA'
#+PHPVersion
#=php82
AddHandler x-httpd-php82 .php
#-PHPVersion
HTA

echo '<?php echo "php-" . PHP_VERSION;' > "$HOME_DIR/public_html/ok.php"
ls -la "$HOME_DIR/public_html/static.txt" "$HOME_DIR/public_html/ok.php"
cat "$HOME_DIR/.htaccess"
cat "$HOME_DIR/public_html/.htaccess"
echo DONE
