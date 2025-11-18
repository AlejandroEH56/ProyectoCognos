<?php

declare(strict_types=1);

namespace App\Router;

use App\Router\Rutes as GroupRutes;

class Router
{

    /**
     * Router simple pero escalable
     */
    function route(string $method, string $path)
    {
        // Rutas definidas
        $goupRoutes = new GroupRutes();
        $routes = $goupRoutes->groupRutes();

        // Validar que exista un grupo de rutas para el método
        if (!isset($routes[$method])) {
            http_response_code(405);
            echo "Método HTTP no permitido.";
            return;
        }

        // Buscar coincidencia exacta o dinámica
        foreach ($routes[$method] as $pattern => $target) {
            $regex = "#^" . $pattern . "$#";

            if (preg_match($regex, $path, $matches)) {
                array_shift($matches); // quitar el match completo
                [$class, $action] = $target;

                if (!class_exists($class)) {
                    throw new \Exception("Controlador $class no encontrado.");
                }

                $controller = new $class();

                if (!method_exists($controller, $action)) {
                    throw new \Exception("Método $action no existe en $class.");
                }

                // Llamar al controlador con parámetros dinámicos si aplica
                return $controller->$action(...array_map('intval', $matches));
                //return $controller->$action(...$matches);
            }
        }

        // Si no se encontró ruta
        http_response_code(404);
        echo "<h1>404</h1><h4>Ruta no encontrada<h4>";
    }
}
