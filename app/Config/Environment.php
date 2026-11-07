<?php

namespace App\Config;

use Dotenv\Dotenv;
use Dotenv\Exception\InvalidFileException;

class Environment
{
    private array $values;

    public function __construct(?string $directory = null)
    {
        try {
            $this->values = Dotenv::createArrayBacked($directory ?? dirname(__DIR__, 2))->safeLoad();
        } catch (InvalidFileException) {
            throw new \RuntimeException('Revisa la sintaxis del archivo .env.');
        }
    }

    public function get(string $name, mixed $default = null): mixed
    {
        $processValue = getenv($name);
        if ($processValue !== false) {
            return $processValue;
        }
        return $_ENV[$name] ?? $_SERVER[$name] ?? $this->values[$name] ?? $default;
    }

    public static function database(string $directory): array
    {
        $environment = new self($directory);
        return self::validateDatabase([
            'host' => $environment->get('DB_HOST'),
            'database' => $environment->get('DB_DATABASE'),
            'username' => $environment->get('DB_USERNAME'),
            'password' => $environment->get('DB_PASSWORD'),
            'port' => $environment->get('DB_PORT', 3306)
        ]);
    }

    public static function validateDatabase(array $values): array
    {
        $config = [];
        foreach (['host' => 'DB_HOST', 'database' => 'DB_DATABASE', 'username' => 'DB_USERNAME', 'password' => 'DB_PASSWORD'] as $key => $name) {
            $value = $values[$key] ?? null;
            if (!is_string($value) || ($key !== 'password' && trim($value) === '')) {
                throw new \RuntimeException('Configura la variable ' . $name . ' en .env o en el entorno del servidor.');
            }
            $config[$key] = $value;
        }

        $port = filter_var($values['port'] ?? 3306, FILTER_VALIDATE_INT, [
            'options' => ['min_range' => 1, 'max_range' => 65535]
        ]);
        if ($port === false) {
            throw new \RuntimeException('DB_PORT debe ser un número entero entre 1 y 65535.');
        }
        $config['port'] = $port;
        return $config;
    }
}
