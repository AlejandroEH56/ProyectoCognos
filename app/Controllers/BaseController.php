<?php

namespace App\Controllers;

/**
 * Servicios HTTP compartidos: sesión, validación de entradas, mensajes y renderizado.
 */
class BaseController
{
    /**
     * Lee un campo POST obligatorio y elimina espacios de sus extremos.
     *
     * Para el límite 65535 mide bytes; para los demás límites mide caracteres UTF-8.
     *
     * @param string $key Nombre del campo en $_POST.
     * @param string $label Nombre legible utilizado en los errores de validación.
     * @param int $maxBytes Límite permitido según la regla de longitud descrita.
     * @return string Texto validado y recortado.
     * @throws \InvalidArgumentException Si falta el campo, no es texto o supera el límite.
     */
    protected function postString(string $key, string $label, int $maxBytes = 255): string
    {
        $value = $_POST[$key] ?? null;
        if (!is_string($value) || trim($value) === '') {
            throw new \InvalidArgumentException('Revisa el campo ' . $label . '.');
        }
        $length = $maxBytes === 65535 ? strlen(trim($value)) : mb_strlen(trim($value), 'UTF-8');
        if ($length > $maxBytes) {
            throw new \InvalidArgumentException('El campo ' . $label . ' excede la longitud permitida.');
        }
        return trim($value);
    }

    /**
     * Lee un campo POST y valida el rango de un entero INT de MySQL.
     *
     * @param string $key Nombre del campo en $_POST.
     * @param string $label Nombre legible del campo para los mensajes.
     * @return int Entero entre -2147483648 y 2147483647, incluido cero.
     * @throws \InvalidArgumentException Si el campo está ausente o no contiene un entero válido.
     */
    protected function postInteger(string $key, string $label): int
    {
        $value = $this->postString($key, $label);
        $integer = filter_var($value, FILTER_VALIDATE_INT, ['options' => ['min_range' => -2147483648, 'max_range' => 2147483647]]);
        if ($integer === false) {
            throw new \InvalidArgumentException('El campo ' . $label . ' debe ser un número entero válido.');
        }
        return $integer;
    }

    /**
     * Guarda un mensaje en sesión y finaliza con una redirección HTTP 303.
     *
     * @param string $route Ruta de destino de la cabecera Location.
     * @param string $message Mensaje que leerá la vista después de redirigir.
     * @param bool $error Si es true utiliza la clave de sesión error; en otro caso, mensaje.
     * @return never Envía la redirección y termina la ejecución.
     */
    protected function redirectMessage(string $route, string $message, bool $error = false): void
    {
        unset($_SESSION[$error ? 'mensaje' : 'error']);
        $_SESSION[$error ? 'error' : 'mensaje'] = $message;
        header('Location: ' . $route, true, 303);
        exit;
    }

    /**
     * Registra una excepción y redirige con un mensaje apropiado para el usuario.
     *
     * @param string $route Ruta a la que se redirige después del fallo.
     * @param \Throwable $error Error de validación o persistencia que se registrará.
     * @return never Delega la redirección y termina la ejecución.
     */
    protected function redirectError(string $route, \Throwable $error): void
    {
        error_log((string)$error);
        $message = 'No se pudo completar la operación. Revisa los datos e intenta nuevamente.';
        if ($error instanceof \InvalidArgumentException) {
            $message = $error->getMessage();
        } elseif ($error instanceof \mysqli_sql_exception && $error->getCode() === 1451) {
            $message = 'Hay registros relacionados que impiden completar la operación.';
        }
        $this->redirectMessage($route, $message, true);
    }

    /**
     * Inicia la sesión si hace falta y ejecuta la inicialización del módulo.
     *
     * @return void Inicializa esta instancia; no admite parámetros.
     * @throws \Throwable Si initialize() no puede preparar sus dependencias.
     */
    final public function __construct()
    {
        $this->startSession();
        $this->initialize();
    }

    /**
     * Permite a los módulos preparar sus dependencias después de abrir la sesión.
     *
     * @return void El comportamiento predeterminado no realiza operaciones.
     */
    protected function initialize(): void
    {
    }

    /**
     * Abre una sesión PHP únicamente cuando no hay una sesión iniciada.
     *
     * @return void Conserva la sesión existente y sus datos.
     */
    private function startSession()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    /**
     * Incluye una vista compartida y expone las claves de data como variables locales.
     *
     * La salida HTML se escribe directamente; si falta la vista imprime un mensaje.
     *
     * @param string $view Nombre de la vista en app/Views, sin extensión.
     * @param array<string, mixed> $data Variables que recibirá la plantilla.
     * @return null No devuelve el HTML generado.
     */
    protected function render(string $view, array $data = [])
    {
        extract($data); // Convierte los elementos del array en variables
        $file = __DIR__ . '/../Views/' . $view . '.php';
        if (!is_file($file)) {
            echo '<h1>Render Error:</h1><p>Vista no encontrada: ' . htmlspecialchars($view, ENT_QUOTES, 'UTF-8') . '</p>';
            return;
        }
        include $file;
    }
    /**
     * Resuelve e incluye una vista según el namespace del controlador concreto.
     *
     * La salida HTML se escribe directamente. Las claves de data se extraen en el ámbito de la vista.
     *
     * @param string $view Nombre de la vista, sin extensión PHP.
     * @param array<string, mixed> $data Variables de entrada para la plantilla.
     * @return null No devuelve el contenido renderizado.
     * @throws \Exception Si el namespace no permite resolver el módulo o la vista no existe.
     */
    public function renderModule($view, $data = [])
    {
        // Obtener el namespace completo de la clase actual
        $classNamespace = get_class($this);  // Ej. 'Modules\FormularioKostick\Controllers\MiController'

        // Extraer partes del namespace
        $parts = explode('\\', $classNamespace);

        // Determinar la ruta base de las vistas
        if ($parts[0] === 'App' && isset($parts[1]) && $parts[1] === 'Controllers') {
            // Es del core: App/Controllers -> App/Views
            $viewPath = __DIR__ . '/../Views/' . $view . '.php';
        } elseif ($parts[0] === 'Modules' && isset($parts[1]) && isset($parts[2]) && $parts[2] === 'Controllers') {
            // Es de un módulo: Modules/NombreModulo/Controllers -> Modules/NombreModulo/Views
            $moduleName = $parts[1];
            $viewPath = __DIR__ . '/../../Modules/' . $moduleName . '/Views/' . $view . '.php';
        } else {
            throw new \Exception("Namespace no reconocido para renderizar vistas: $classNamespace");
        }

        // Verificar si la vista existe
        if (!file_exists($viewPath)) {
            throw new \Exception("Vista no encontrada: $viewPath");
        }

        // Extraer datos a variables locales (opcional, para simplicidad)
        extract($data);

        // Incluir la vista
        include $viewPath;
    }
}
