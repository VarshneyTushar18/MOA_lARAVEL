#!/bin/bash
APP="/home/sites/41b/b/ba690bc503/MOA_lARAVEL"
PUB="/home/sites/41b/b/ba690bc503/public_html"
echo "=== frontend.blade.php on server ==="
grep -n "aiia-header\|ministry-ayush" "$APP/resources/views/layout/frontend.blade.php" | head -5
echo "=== image files ==="
ls -la "$PUB/assets/images/aiia-header"* 2>&1
echo "=== curl local index snippet ==="
grep -o 'assets/images/[^"]*aiia[^"]*' "$PUB/index.php" 2>/dev/null || true
php82 -r 'echo file_exists("'$PUB'/assets/images/aiia-header-brand.png") ? "brand exists\n" : "brand MISSING\n";'
php82 -r 'echo file_exists("'$PUB'/assets/images/aiia-header-logo.png") ? "logo exists\n" : "logo MISSING\n";'
