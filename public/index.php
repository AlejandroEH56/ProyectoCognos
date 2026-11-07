<?php

use App\Router\Router;

require_once dirname(__DIR__) . '/vendor/autoload.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

try {
    $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
    $path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '/';
    if ($path === '/index.php') {
        $path = '/';
    }
    (new Router())->route($method, $path);
} catch (Throwable $error) {
    http_response_code(500);
    echo 'Error interno del servidor: ' . htmlspecialchars($error->getMessage(), ENT_QUOTES, 'UTF-8');
}
