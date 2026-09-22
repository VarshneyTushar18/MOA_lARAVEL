#!/bin/bash
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
