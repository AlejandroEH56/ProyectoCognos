<?php

namespace App\Controllers;

class BaseController
{
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

    protected function postInteger(string $key, string $label): int
    {
        $value = $this->postString($key, $label);
        $integer = filter_var($value, FILTER_VALIDATE_INT, ['options' => ['min_range' => -2147483648, 'max_range' => 2147483647]]);
        if ($integer === false) {
            throw new \InvalidArgumentException('El campo ' . $label . ' debe ser un número entero válido.');
        }
        return $integer;
    }

    protected function redirectMessage(string $route, string $message, bool $error = false): void
    {
        unset($_SESSION[$error ? 'mensaje' : 'error']);
        $_SESSION[$error ? 'error' : 'mensaje'] = $message;
        header('Location: ' . $route, true, 303);
        exit;
    }

    protected function redirectError(string $route, \Throwable $error): void
    {
        error_log((string)$error);
        $message = 'No se pudo completar la operación. Revisa los datos e intenta nuevamente.';
        if ($error instanceof \InvalidArgumentException) {
            $message = $error->getMessage();
        } elseif ($error instanceof \mysqli_sql_exception && $error->getCode() === 1451) {
            $message = 'Hay respuestas registradas que utilizan estas opciones. Debes conservarlas.';
        }
        $this->redirectMessage($route, $message, true);
    }

    protected function evaluationData(string $test): array
    {
        $model = new \App\Models\EvaluacionModel();
        $token = bin2hex(random_bytes(16));
        $_SESSION['formularios_evaluacion'][$token] = ['test' => $test, 'catalogo' => $model->catalogHash($test)];
        return [
            'personas' => $model->getPersonas(), 'perfiles' => $model->getPerfiles(), 'token' => $token,
            'anteriores' => $_SESSION['evaluacion_anterior'][$test] ?? []
        ];
    }

    protected function submitEvaluation(string $test, string $route): void
    {
        try {
            $token = $this->postString('evaluacion_token', 'formulario');
            $form = $_SESSION['formularios_evaluacion'][$token] ?? null;
            if (!$form || $form['test'] !== $test || !preg_match('/^[a-f0-9]{32}$/', $token)) {
                throw new \InvalidArgumentException('El formulario expiró. Recarga la evaluación antes de enviarla.');
            }
            $responses = $_POST['respuestas'] ?? null;
            if (!is_array($responses)) {
                throw new \InvalidArgumentException('Responde el cuestionario antes de enviarlo.');
            }
            $id = (new \App\Models\EvaluacionModel())->guardar(
                $test, $this->postString('id_persona', 'persona'), $this->postString('id_perfil', 'perfil'),
                $responses, $token, $form['catalogo']
            );
            unset($_SESSION['evaluacion_anterior'][$test]);
            $this->redirectMessage($route, 'Evaluación guardada correctamente. Aplicación ' . $id . '.');
        } catch (\Throwable $error) {
            $_SESSION['evaluacion_anterior'][$test] = array_intersect_key($_POST, array_flip(['id_persona', 'id_perfil', 'respuestas']));
            $this->redirectError($route, $error);
        }
    }

    public function __construct()
    {
        $this->startSession();
    }

    private function startSession()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

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
     * Renderiza una vista.
     * Detecta automáticamente si es del core (App/Views) o de un módulo (Modules/NombreModulo/Views).
     * @param string $view Nombre del archivo de vista (sin extensión, ej. 'index').
     * @param array $data Datos a pasar a la vista.
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
