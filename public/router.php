<?php

// Enrutador para php -S. Apache utiliza public/.htaccess.
if (PHP_SAPI !== 'cli-server') {
    http_response_code(404);
    exit;
}

$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$file = realpath(__DIR__ . '/' . ltrim(rawurldecode($path ?? '/'), '/'));
$publicDirectory = __DIR__ . DIRECTORY_SEPARATOR;
if ($file !== false && str_starts_with($file, $publicDirectory) && is_file($file)
    && pathinfo($file, PATHINFO_EXTENSION) !== 'php' && !str_starts_with(basename($file), '.')) {
    return false;
}

require __DIR__ . '/index.php';
