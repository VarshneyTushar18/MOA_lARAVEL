#!/bin/bash
set -e
HOME_DIR="/home/sites/41b/b/ba690bc503"
APP="$HOME_DIR/MOA_lARAVEL"
PUB="$HOME_DIR/public_html"

# Remove files that should not be web-accessible
rm -f "$PUB/laravel_db_backup.sql" "$PUB/debug.php" "$PUB/bare.txt" "$PUB/ok.php" "$PUB/static.txt" "$PUB/hello.html" "$PUB/.htaccess.bak"

# Account PHP version (StackCP standard)
cat > "$HOME_DIR/.htaccess" <<'HTA'
#+PHPVersion
#=php82
AddHandler x-httpd-php82 .php
#-PHPVersion
HTA

# Laravel public htaccess
cat > "$PUB/.htaccess" <<'HTA'
<IfModule mod_rewrite.c>
    Options +FollowSymLinks -MultiViews -Indexes
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

DirectoryIndex index.php index.html
HTA

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
$response = $kernel->handle($request = Request::capture())->send();
$kernel->terminate($request, $response);
PHP

echo 'static-ok' > "$PUB/ping.txt"

# Permissions
chmod 711 "$HOME_DIR"
chmod 755 "$PUB"
find "$PUB" -type f -exec chmod 644 {} \;
find "$PUB" -type d -exec chmod 755 {} \;
chmod -R u+rwX "$APP/storage" "$APP/bootstrap/cache"

# Storage link
rm -f "$PUB/storage"
ln -sfn "$APP/storage/app/public" "$PUB/storage"

cd "$APP"
php82 artisan config:cache
php82 artisan route:cache
php82 artisan view:cache

# Local CLI sanity check
php82 -r "require '$PUB/index.php';" 2>&1 | head -3 || true
echo FIX_DONE
