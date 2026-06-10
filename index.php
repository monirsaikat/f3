<?php

require __DIR__ . '/vendor/autoload.php';

$f3 = Base::instance();

$f3->route('GET /', function () {
    echo 'Hello, World!';
});

$f3->run();
