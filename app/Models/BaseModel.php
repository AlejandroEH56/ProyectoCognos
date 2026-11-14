<?php

namespace App\Models;

use App\Config\Database;
use mysqli;
use Exception;

/**
 * Persistencia general con consultas preparadas, conexión compartida y transacciones.
 */
class BaseModel
{
    // Propiedades configurables en clases hijas
    /**
     * @var string Tabla configurada por el módulo; admite el formato esquema.tabla.
     */
    protected string $table;          // Nombre de tabla, opcionalmente esquema.tabla.
    /**
     * @var string Nombre de la columna que identifica de forma única cada registro.
     */
    protected string $primaryKey = 'id';  // Clave primaria simple.
    /**
     * @var list<string> Columnas permitidas en las operaciones create() y update().
     */
    protected array $fillable = [];   // Campos permitidos para insert/update (seguridad)
    /**
     * @var list<string> Nombres reservados para fechas; no se completan automáticamente.
     */
    protected array $timestamps = []; // Campos de timestamp si aplica (e.g., ['created_at', 'updated_at'])
    /**
     * @var \mysqli|null Conexión compartida por los modelos durante el proceso actual.
     */
    protected static ?mysqli $instance = null;
    /**
     * @var int Profundidad de las transacciones administradas y sus savepoints.
     */
    private static int $transactionDepth = 0;
    /**
     * @var \mysqli Conexión utilizada por esta instancia del modelo.
     */
    protected $db;

    /**
     * Asigna a la instancia la conexión compartida disponible.
     *
     * @return void Inicializa el acceso a persistencia.
     * @throws \Exception Si no puede obtener una conexión válida.
     */
    public function __construct()
    {
        $this->db = self::db();
    }

    /**
     * Obtiene la conexión compartida sin crear una conexión por consulta.
     *
     * @return \mysqli Conexión utilizada para preparar las sentencias.
     * @throws \Exception Si la conexión no está disponible.
     */
    protected function connect(): mysqli
    {
        return $this->db();
    }

    /**
     * Genera un identificador con prefijo y 16 bytes aleatorios en hexadecimal.
     *
     * @param string $prefix Prefijo que identifica el tipo de registro.
     * @return string Identificador con el formato prefijo-hexadecimal.
     * @throws \Random\RandomException Si no se pueden obtener bytes aleatorios.
     */
    public static function newId(string $prefix): string
    {
        return $prefix . '-' . bin2hex(random_bytes(16));
    }

    /**
     * Ejecuta una operación atómica; las llamadas anidadas utilizan savepoints.
     *
     * @template TResult
     * @param callable():TResult $operation Operación que comparte la conexión del modelo.
     * @return TResult Resultado devuelto por la operación después de confirmar sus escrituras.
     * @throws \Throwable Si falla la operación o la gestión de la transacción; revierte lo correspondiente.
     */
    public function transaction(callable $operation): mixed
    {
        $level = self::$transactionDepth;
        $savepoint = 'model_' . $level;
        if ($level === 0) {
            $this->db->begin_transaction();
        } else {
            $this->db->query('SAVEPOINT ' . $savepoint);
        }
        self::$transactionDepth++;
        try {
            $result = $operation();
            if ($level === 0) {
                $this->db->commit();
            } else {
                $this->db->query('RELEASE SAVEPOINT ' . $savepoint);
            }
            return $result;
        } catch (\Throwable $error) {
            if ($level === 0) {
                $this->db->rollback();
            } else {
                $this->db->query('ROLLBACK TO SAVEPOINT ' . $savepoint);
            }
            throw $error;
        } finally {
            self::$transactionDepth--;
        }
    }

    /**
     * Obtiene o abre la conexión compartida y registra los fallos de conexión.
     *
     * @return \mysqli|null Conexión disponible; null si una excepción impidió obtenerla.
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
     * Obtiene una conexión disponible o informa de su ausencia.
     *
     * @return \mysqli Conexión compartida de persistencia.
     * @throws \Exception Si getInstance() no proporciona una conexión válida.
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
     * Ejecuta una lectura SQL preparada y devuelve todas sus filas.
     *
     * @param string $sql Consulta SELECT con marcadores de posición ?.
     * @param list<mixed> $params Valores en el mismo orden que los marcadores SQL.
     * @return list<array<string, mixed>> Filas asociativas; lista vacía cuando no hay resultados.
     * @throws \Exception Si no hay conexión o falla la preparación o ejecución SQL.
     */
    protected function queryPrepared(string $sql, array $params = []): array
    {
        $conn = $this->connect();
        $stmt = $conn->prepare($sql);
        if (!$stmt) throw new Exception('Error al preparar la consulta: ' . $conn->error);
        try {
            if ($params) {
                $stmt->bind_param($this->getParamTypes($params), ...$params);
            }
            if (!$stmt->execute()) throw new Exception('Error al ejecutar la consulta: ' . $stmt->error);
            return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        } finally {
            $stmt->close();
        }
    }


    /**
     * Obtiene la primera fila de una lectura preparada.
     *
     * La consulta determina el orden y el límite de los resultados.
     *
     * @param string $sql Consulta SELECT con marcadores de posición ?.
     * @param list<mixed> $params Valores ordenados de los marcadores SQL.
     * @return array<string, mixed>|null Primera fila asociativa, o null si no hay filas.
     * @throws \Exception Si falla la conexión o la consulta.
     */
    protected function queryOne(string $sql, array $params = []): ?array
    {
        return $this->queryPrepared($sql, $params)[0] ?? null;
    }

    /**
     * Busca un registro por la clave primaria configurada en el modelo.
     *
     * @param int|string $id Valor de la clave primaria.
     * @param bool $lock Si es true añade FOR UPDATE; requiere transaction() activa.
     * @return array<string, mixed>|null Registro encontrado, o null si la clave no existe.
     * @throws \LogicException Si se pide bloqueo sin transacción o hay identificadores SQL inválidos.
     * @throws \Exception Si falla la conexión o la lectura SQL.
     */
    public function find($id, bool $lock = false): ?array
    {
        if ($lock && self::$transactionDepth === 0) {
            throw new \LogicException('La búsqueda con bloqueo requiere una transacción activa.');
        }
        $table = $this->quoteIdentifier($this->table, true);
        $key = $this->quoteIdentifier($this->primaryKey);
        return $this->queryOne("SELECT * FROM $table WHERE $key = ?" . ($lock ? ' FOR UPDATE' : ''), [$id]);
    }

    /**
     * Comprueba si una clave primaria está presente en la tabla configurada.
     *
     * @param int|string $id Clave primaria que se busca como parámetro SQL.
     * @return bool True cuando existe una fila con esa clave; false en otro caso.
     * @throws \LogicException Si la tabla o la clave configurada no es un identificador válido.
     * @throws \Exception Si falla la conexión o la consulta.
     */
    public function exists($id): bool
    {
        $table = $this->quoteIdentifier($this->table, true);
        $key = $this->quoteIdentifier($this->primaryKey);
        return $this->queryOne("SELECT 1 AS found FROM $table WHERE $key = ? LIMIT 1", [$id]) !== null;
    }

    /**
     * Cuenta todos los registros de la tabla, sin aplicar reglas de estado.
     *
     * @return int Número total de filas, incluido cero para una tabla vacía.
     * @throws \LogicException Si la tabla configurada no es un identificador válido.
     * @throws \Exception Si falla la conexión o la consulta.
     */
    public function count(): int
    {
        $table = $this->quoteIdentifier($this->table, true);
        return (int)($this->queryOne("SELECT COUNT(*) AS total FROM $table")['total'] ?? 0);
    }

    /**
     * Valida y entrecomilla un identificador SQL configurado por el desarrollador.
     *
     * @param string $identifier Nombre simple o nombre cualificado que se validará.
     * @param bool $qualified Permite dos componentes separados por un punto cuando es true.
     * @return string Identificador entrecomillado con acentos graves por componente.
     * @throws \LogicException Si el nombre contiene componentes o caracteres no admitidos.
     */
    private function quoteIdentifier(string $identifier, bool $qualified = false): string
    {
        $parts = explode('.', $identifier);
        if (count($parts) > ($qualified ? 2 : 1)) {
            throw new \LogicException('Identificador SQL inválido en la configuración del modelo.');
        }
        foreach ($parts as $part) {
            if (!preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/D', $part)) {
                throw new \LogicException('Identificador SQL inválido en la configuración del modelo.');
            }
        }
        return implode('.', array_map(fn($part) => '`' . $part . '`', $parts));
    }

    /**
     * Ejecuta una escritura SQL preparada y conserva el conteo de filas modificadas.
     *
     * @param string $sql Sentencia INSERT, UPDATE o DELETE con marcadores ?.
     * @param list<mixed> $params Valores en el orden de los marcadores SQL.
     * @return int Filas modificadas; cero también puede indicar valores idénticos en UPDATE.
     * @throws \Exception Si no hay conexión o falla la preparación o ejecución.
     */
    protected function executePrepared(string $sql, array $params = []): int
    {
        $conn = $this->connect();
        $stmt = $conn->prepare($sql);
        if (!$stmt) throw new Exception('Error al preparar la consulta: ' . $conn->error);
        try {
            if ($params) {
                $stmt->bind_param($this->getParamTypes($params), ...$params);
            }
            if (!$stmt->execute()) throw new Exception('Error al ejecutar la consulta: ' . $stmt->error);
            return $stmt->affected_rows;
        } finally {
            $stmt->close();
        }
    }

    /**
     * Construye la cadena de tipos que requiere mysqli_stmt::bind_param().
     *
     * @param list<mixed> $params Valores que se enlazarán a la sentencia preparada.
     * @return string Un carácter por parámetro: i, d, s o b; NULL se enlaza con tipo s.
     */
    private function getParamTypes(array $params): string
    {
        $types = '';
        foreach ($params as $param) {
            if (is_int($param)) $types .= 'i';
            elseif (is_float($param)) $types .= 'd';
            elseif (is_string($param)) $types .= 's';
            elseif ($param === null) $types .= 's';
            else $types .= 'b';
        }
        return $types;
    }

    /**
     * Conserva únicamente las claves declaradas como campos permitidos.
     *
     * @param array<string, mixed> $data Datos proporcionados a una operación de escritura.
     * @return array<string, mixed> Datos filtrados sin modificar sus valores.
     */
    private function filterFillable(array $data): array
    {
        return array_intersect_key($data, array_flip($this->fillable));
    }


    /**
     * Inserta un registro con los campos permitidos del modelo.
     *
     * @param array<string, mixed> $data Valores por nombre de columna; las claves ajenas se descartan.
     * @return bool True si se insertó una fila; false si la sentencia no insertó filas.
     * @throws \LogicException Si un identificador SQL configurado es inválido.
     * @throws \Exception Si no hay campos permitidos o falla la persistencia.
     */
    public function create(array $data): bool
    {
        $data = $this->filterFillable($data);
        if (empty($data)) {
            throw new Exception('No hay datos válidos para insertar. (model)');
        }
        $columns = implode(', ', array_map(fn($key) => $this->quoteIdentifier($key), array_keys($data)));
        $table = $this->quoteIdentifier($this->table, true);
        $placeholders = str_repeat('?, ', count($data) - 1) . '?';
        $sql = "INSERT INTO $table ($columns) VALUES ($placeholders)";
        $affected = $this->executePrepared($sql, array_values($data));
        return $affected > 0;
    }

    /**
     * Guarda un registro existente, incluso cuando los valores enviados no cambian.
     *
     * Comprueba y bloquea la fila dentro de una transacción antes de actualizarla.
     *
     * @param int|string $id Clave primaria del registro que se desea guardar.
     * @param array<string, mixed> $data Campos y valores; solo se conservan los permitidos.
     * @return bool True si existe y el guardado tiene éxito; false si el ID no existe.
     * @throws \LogicException Si los identificadores SQL del modelo son inválidos.
     * @throws \Exception Si no hay campos permitidos o falla la persistencia; se revierte la operación.
     */
    public function update($id, array $data): bool
    {
        $data = $this->filterFillable($data);
        if (empty($data)) {
            throw new Exception('No hay datos válidos para actualizar.');
        }
        $table = $this->quoteIdentifier($this->table, true);
        $key = $this->quoteIdentifier($this->primaryKey);
        $set = implode(', ', array_map(fn($column) => $this->quoteIdentifier($column) . ' = ?', array_keys($data)));
        $sql = "UPDATE $table SET $set WHERE $key = ?";
        $params = array_values($data);
        $params[] = $id;
        return $this->transaction(function () use ($id, $sql, $params) {
            if ($this->find($id, true) === null) {
                return false;
            }
            $this->executePrepared($sql, $params);
            return true;
        });
    }

    /**
     * Elimina físicamente una fila por su clave primaria.
     *
     * @param int|string $id Clave primaria de la fila que se eliminará.
     * @return bool True si eliminó una fila; false si no encontró la clave.
     * @throws \LogicException Si la tabla o clave configurada es inválida.
     * @throws \Exception Si falla la persistencia, incluidas restricciones de claves foráneas.
     */
    public function delete($id): bool
    {
        $table = $this->quoteIdentifier($this->table, true);
        $key = $this->quoteIdentifier($this->primaryKey);
        $sql = "DELETE FROM $table WHERE $key = ?";
        return $this->executePrepared($sql, [$id]) > 0;
    }
}
