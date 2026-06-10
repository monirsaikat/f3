<?php

require __DIR__ . '/vendor/autoload.php';

use App\Support\Database;
use App\Support\Localization;

// PHP built-in server (php -S): serve existing static files directly.
// Apache does this via .htaccess (RewriteCond !-f), so this block is dev-only.
if (PHP_SAPI === 'cli-server') {
    $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    if ($path !== '/' && is_file(__DIR__ . $path)) {
        return false;
    }
}

$f3 = Base::instance();

$f3->config(__DIR__ . '/config/config.ini');
$f3->config(__DIR__ . '/config/db.ini');
$f3->config(__DIR__ . '/config/routes.ini');

// Views and translations
$f3->set('UI', __DIR__ . '/ui/');
$f3->set('LOCALES', __DIR__ . '/dict/');
$f3->set('FALLBACK', 'en');
$f3->set('ENCODING', 'UTF-8');

// Resolve the UI language (?lang / cookie / fallback).
Localization::boot($f3);

// Connect to MySQL and self-provision the schema (no migrations to run).
Database::boot($f3);

// Errors as JSON so API clients never get an HTML page
$f3->set('ONERROR', function (Base $f3) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'status'  => 'error',
        'code'    => $f3->get('ERROR.code'),
        'message' => $f3->get('ERROR.text'),
        'trace'   => $f3->get('DEBUG') >= 2 ? $f3->get('ERROR.trace') : null,
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
});

$f3->run();
