<?php

use App\Router\Router;

require_once 'vendor/autoload.php';

session_start();

/**
 * Dispatcher principal
 */
try {
    $method = $_SERVER['REQUEST_METHOD'];
    $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $ruter = new Router();
    $ruter->route($method, $path);
} catch (Throwable $e) {
    http_response_code(500);
    echo "Error interno del servidor: " . htmlspecialchars($e->getMessage());
}
