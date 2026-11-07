<?php

namespace App\Config;

use mysqli;
use Exception;

class Database
{
    private $host;
    private $username;
    private $password;
    private $database;
    private int $port = 3306;
    private ?mysqli $connection = null;


    public function __construct()
    {
        $config = require __DIR__ . '/ConfigDB.php';
        if (!is_array($config) || empty($config['host']) || empty($config['database'])) {
            throw new Exception('Configuración de DB inválida o incompleta en ConfigDB.php');
        }

        $this->host = $config['host'];
        $this->username = $config['username'];
        $this->password = $config['password'];
        $this->database = $config['database'];
        $this->port = (int)($config['port'] ?? 3306);

        $this->connect();
    }

    /**
     * Conexión principal a la BD. Lanza excepción en fallo en lugar de die().
     * @return void
     * @throws Exception Si falla la conexión.
     */
    public function connect(): void
    {
        $this->connection = new mysqli($this->host, $this->username, $this->password, $this->database, $this->port);
        if ($this->connection->connect_error) {
            $error = "Conexión fallida: " . $this->connection->connect_error;
            error_log($error); // Log para depuración
            throw new Exception($error);
        }
        // Opcional: Set charset para UTF-8
        $this->connection->set_charset('utf8mb4');
    }

    /**
     * Obtiene la conexión. Retorna null si no conectada.
     * @return mysqli|null
     */
    public function getConnection(): ?mysqli
    {
        if ($this->connection === null) {
            try {
                $this->connect(); // Reintento lazy si se perdió
            } catch (Exception $e) {
                error_log("Reintento de conexión fallido: " . $e->getMessage());
                return null;
            }
        }
        return $this->connection;
    }
    /**
     * Cerrar la conexion a la base de datos
     * @return void
     */
    public function closeConnection(): void
    {
        if ($this->connection) {
            $this->connection->close();
        }
    }
}
