#!/bin/bash
set -e
HOME_DIR="/home/sites/41b/b/ba690bc503"
APP="$HOME_DIR/MOA_lARAVEL"
PUB="$HOME_DIR/public_html"

rm -f "$PUB"
mkdir -p "$PUB"
cp -a "$APP/public/." "$PUB/"

cat > "$PUB/index.php" <<'PHP'
<?php

use Illuminate\Contracts\Http\Kernel;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

if (file_exists($maintenance = __DIR__.'/../MOA_lARAVEL/storage/framework/maintenance.php')) {
    require $maintenance;
}

require __DIR__.'/../MOA_lARAVEL/vendor/autoload.php';

$app = require_once __DIR__.'/../MOA_lARAVEL/bootstrap/app.php';

$kernel = $app->make(Kernel::class);

$response = $kernel->handle(
    $request = Request::capture()
)->send();

$kernel->terminate($request, $response);
PHP

cat > "$HOME_DIR/.htaccess" <<'HTA'
#+PHPVersion
#=php82
AddHandler x-httpd-php82 .php
#-PHPVersion
HTA

cat > "$PUB/.htaccess" <<'HTA'
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

rm -f "$PUB/storage"
ln -sfn "$APP/storage/app/public" "$PUB/storage"

chmod -R u+rwX "$APP/storage" "$APP/bootstrap/cache"
cd "$APP"
php82 artisan config:cache
echo '<?php echo "ok-php-" . PHP_VERSION;' > "$PUB/ok.php"
echo DONE
