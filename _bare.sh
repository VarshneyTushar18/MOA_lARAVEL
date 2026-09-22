#!/bin/bash
HOME_DIR="/home/sites/41b/b/ba690bc503"
rm -f "$HOME_DIR/.htaccess"
rm -f "$HOME_DIR/public_html/.htaccess"
echo 'bare-static' > "$HOME_DIR/public_html/bare.txt"
ls -la "$HOME_DIR/.htaccess" "$HOME_DIR/public_html/.htaccess" 2>&1 || true
ls -la "$HOME_DIR/public_html/bare.txt"
echo DONE
