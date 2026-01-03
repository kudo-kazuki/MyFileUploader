<?php
set_exception_handler(function ($e) {
    $code = $e->getCode();
    if (!is_int($code) || $code < 400 || $code >= 600) {
        $code = 500;
    }

    error($e->getMessage(), $code);
});

require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/router.php';

use Core\Router;

$router = new Router();
$router->dispatch($_SERVER['REQUEST_URI'], $_SERVER['REQUEST_METHOD']);
