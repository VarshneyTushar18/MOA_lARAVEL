#!/bin/bash
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
