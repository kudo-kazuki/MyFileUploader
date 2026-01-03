<?php
require_once __DIR__ . '/../vendor/autoload.php';

$env = getenv('APP_ENV') ?: 'local';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__, ".env.$env");
$dotenv->safeLoad();

date_default_timezone_set('Asia/Tokyo');

// 共通関数
require_once __DIR__ . '/Helpers/request.php';
require_once __DIR__ . '/Helpers/response.php';
