#!/bin/bash
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
