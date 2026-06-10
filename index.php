<?php

require __DIR__ . '/vendor/autoload.php';

use App\Support\Database;

$f3 = Base::instance();

$f3->config(__DIR__ . '/config/config.ini');
$f3->config(__DIR__ . '/config/db.ini');
$f3->config(__DIR__ . '/config/routes.ini');

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
