<?php

use App\Config\Environment;

$env = new Environment();

return [
    'host' => $env->get('DB_HOST'),
    'port' => $env->get('DB_PORT', 3306),
    'username' => $env->get('DB_USERNAME'),
    'password' => $env->get('DB_PASSWORD'),
    'database' => $env->get('DB_DATABASE'),
];
