<?php

/**
 * Router for PHP's built-in server (used by `php artisan serve` when this file exists).
 * Serves static assets from public/ so CSS, JS, and images work regardless of CWD.
 */

$uri = urldecode(
    parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? ''
);

$publicFile = __DIR__.'/public'.$uri;

if ($uri !== '/' && $uri !== '' && is_file($publicFile)) {
    $extension = strtolower(pathinfo($publicFile, PATHINFO_EXTENSION));

    $mimeTypes = [
        'css' => 'text/css; charset=UTF-8',
        'js' => 'application/javascript; charset=UTF-8',
        'json' => 'application/json; charset=UTF-8',
        'map' => 'application/json; charset=UTF-8',
        'png' => 'image/png',
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'gif' => 'image/gif',
        'webp' => 'image/webp',
        'svg' => 'image/svg+xml',
        'ico' => 'image/x-icon',
        'woff' => 'font/woff',
        'woff2' => 'font/woff2',
        'ttf' => 'font/ttf',
        'eot' => 'application/vnd.ms-fontobject',
        'mp4' => 'video/mp4',
        'webm' => 'video/webm',
        'mp3' => 'audio/mpeg',
        'wav' => 'audio/wav',
        'pdf' => 'application/pdf',
        'txt' => 'text/plain; charset=UTF-8',
        'xml' => 'application/xml; charset=UTF-8',
        'htm' => 'text/html; charset=UTF-8',
        'html' => 'text/html; charset=UTF-8',
    ];

    if (isset($mimeTypes[$extension])) {
        header('Content-Type: '.$mimeTypes[$extension]);
    }

    header('Content-Length: '.(string) filesize($publicFile));
    readfile($publicFile);

    return true;
}

require_once __DIR__.'/public/index.php';
