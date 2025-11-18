<?php

namespace App\Models;

use App\Config\Database;
use mysqli;
use Exception;

class BaseModel
{
    // Propiedades configurables en clases hijas
    protected string $table;          // Nombre de la tabla (e.g., 'db_cognos.persona')
    protected string $primaryKey = 'id';  // Clave primaria (e.g., 'id_persona')
    protected array $fillable = [];   // Campos permitidos para insert/update (seguridad)
    protected array $timestamps = []; // Campos de timestamp si aplica (e.g., ['created_at', 'updated_at'])
    protected static ?mysqli $instance = null;
    protected $db;

    public function __construct()
    {
        $this->db = self::db();
    }

    protected function connect(): mysqli
    {
        $database = new Database();
        $connection = $database->getConnection();
        if ($connection === null || $connection->connect_error) {
            throw new Exception('Error de conexión: ' . $connection->connect_error);
        }
        return $connection;
    }

    /**
     * Obtiene la instancia singleton de la conexión DB (lazy loading).
     * @return mysqli|null
     * @throws Exception Si falla la creación.
     */
    public static function getInstance(): ?mysqli
    {
        if (self::$instance === null) {
            try {
                $database = new Database();
                self::$instance = $database->getConnection();
                if (self::$instance === null) {
                    throw new Exception('No se pudo obtener conexión válida de Database');
                }
            } catch (Exception $e) {
                error_log("Error en BaseModel::getInstance(): " . $e->getMessage());
                self::$instance = null; // Asegura null en fallo
            }
        }
        return self::$instance;
    }

    /**
     * Método helper para modelos hijas: Obtiene DB o lanza error.
     * Úsalo en queries: $db = $this->getDb();
     * @return mysqli
     * @throws Exception Si no hay conexión.
     */
    protected function db(): mysqli
    {
        $db = static::getInstance();
        if ($db === null) {
            throw new Exception('Conexión a DB no disponible. Verifica configuración.');
        }
        return $db;
    }

    /**
     * Ejecuta una consulta preparada y devuelve resultados.
     * @param string $sql Consulta con placeholders (?).
     * @param array $params Parámetros para bind.
     * @throws \Exception Error en la ejecucion.
     * @return array Resultados como array asociativo.
     */
    protected function queryPrepared(string $sql, array $params = []): array
    {
        $conn = $this->connect();
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            $conn->close();
            throw new Exception('Error al preparar la consulta: ' . $conn->error);
        }
        if (!empty($params)) {
            $types = $this->getParamTypes($params);
            $stmt->bind_param($types, ...$params);
        }
        if (!$stmt->execute()) {
            $stmt->close();
            $conn->close();
            throw new Exception('Error al ejecutar la consulta: ' . $stmt->error);
        }
        $result = $stmt->get_result();
        $data = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        $conn->close();
        return $data;
    }


    /**
     * Ejecuta una consulta preparada (INSERT/UPDATE/DELETE) y devuelve filas afectadas.
     * @param string $sql Consulta preparada.
     * @param array $params Parámetros.
     * @return int Filas afectadas.
     */
    protected function executePrepared(string $sql, array $params = []): int
    {
        $conn = $this->connect();
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            $conn->close();
            throw new Exception('Error al preparar la consulta: ' . $conn->error);
        }
        if (!empty($params)) {
            $types = $this->getParamTypes($params);
            $stmt->bind_param($types, ...$params);
        }
        if (!$stmt->execute()) {
            $stmt->close();
            $conn->close();
            throw new Exception('Error al ejecutar la consulta: ' . $stmt->error);
        }
        $affectedRows = $stmt->affected_rows;
        $stmt->close();
        $conn->close();
        return $affectedRows;
    }

    /**
     * Determina tipos de parámetros para bind_param.
     */
    private function getParamTypes(array $params): string
    {
        $types = '';
        foreach ($params as $param) {
            if (is_int($param)) $types .= 'i';
            elseif (is_float($param)) $types .= 'd';
            elseif (is_string($param)) $types .= 's';
            else $types .= 'b';
        }
        return $types;
    }

    /**
     * Filtra datos para solo incluir campos en $fillable.
     */
    private function filterFillable(array $data): array
    {
        return array_intersect_key($data, array_flip($this->fillable));
    }


    /**
     * Crea un nuevo registro.
     * @param array $data Datos a insertar (solo campos en $fillable).
     * @throws \Exception
     * @return bool true si se inserto el registro, false si falla.
     */
    public function create(array $data): bool
    {
        $data = $this->filterFillable($data);
        if (empty($data)) {
            throw new Exception('No hay datos válidos para insertar. (model)');
        }
        $columns = implode(', ', array_keys($data));
        $placeholders = str_repeat('?, ', count($data) - 1) . '?';
        $sql = "INSERT INTO {$this->table} ($columns) VALUES ($placeholders)";
        $affected = $this->executePrepared($sql, array_values($data));
        //return $affected > 0 ? $this->connect()->insert_id : 0;
        return $affected > 0 ? true : false;
    }

    /**
     * Actualiza un registro por ID.
     * @param mixed $id ID del registro.
     * @param array $data Datos a actualizar (solo campos en $fillable).
     * @return bool True si se actualizó.
     */
    public function update($id, array $data): bool
    {
        $data = $this->filterFillable($data);
        if (empty($data)) {
            throw new Exception('No hay datos válidos para actualizar.');
        }
        $set = implode(', ', array_map(fn($key) => "$key = ?", array_keys($data)));
        $sql = "UPDATE {$this->table} SET $set WHERE {$this->primaryKey} = ?";
        $params = array_values($data);
        $params[] = $id;
        return $this->executePrepared($sql, $params) > 0;
    }

    /**
     * Elimina un registro por ID (hard delete).
     * @param mixed $id ID del registro.
     * @return bool True si se eliminó.
     */
    public function delete($id): bool
    {
        $sql = "DELETE FROM {$this->table} WHERE {$this->primaryKey} = ?";
        return $this->executePrepared($sql, [$id]) > 0;
    }
}
