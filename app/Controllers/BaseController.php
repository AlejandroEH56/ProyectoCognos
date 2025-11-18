<?php

namespace App\Controllers;

class BaseController
{
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
