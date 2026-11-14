<?php

namespace Modules\PerfilProfesional\Controllers;

use App\Controllers\BaseController;
use Modules\PerfilProfesional\Models\PerfilProfesionalModel;

/**
 * Acciones HTTP para listar, crear y editar el catálogo de perfiles profesionales.
 */
class PerfilProfesional extends BaseController
{
    /**
     * Muestra el catálogo completo, los mensajes y el nombre de una captura fallida.
     *
     * Entrada de sesión: mensaje, error y perfil_anterior; limpia este último después de renderizar.
     *
     * @return void Escribe la página HTML del catálogo.
     * @throws \Exception Si falla la lectura de perfiles o la resolución de la vista.
     */
    public function index(): void
    {
        $this->renderModule('listaPerfiles', [
            'perfiles' => (new PerfilProfesionalModel())->getPerfiles(false),
            'mensaje' => $_SESSION['mensaje'] ?? '',
            'error' => $_SESSION['error'] ?? '',
            'anterior' => $_SESSION['perfil_anterior'] ?? ''
        ]);
        unset($_SESSION['perfil_anterior']);
    }

    /**
     * Crea un perfil activo con el nombre recibido por POST.
     *
     * Entrada POST: nombre_perfil, hasta 64 caracteres. Conserva el nombre en sesión ante errores.
     *
     * @return never Redirige a /perfiles-profesionales con la confirmación o el error.
     */
    public function create(): void
    {
        try {
            (new PerfilProfesionalModel())->guardarPerfil(null, $this->postString('nombre_perfil', 'nombre del perfil', 64));
            unset($_SESSION['perfil_anterior']);
            $this->redirectMessage('/perfiles-profesionales', 'Perfil profesional creado correctamente.');
        } catch (\Throwable $error) {
            $_SESSION['perfil_anterior'] = is_string($_POST['nombre_perfil'] ?? null) ? $_POST['nombre_perfil'] : '';
            $this->redirectError('/perfiles-profesionales', $error);
        }
    }

    /**
     * Modifica el nombre y estado de un perfil existente.
     *
     * Entrada POST: id_perfil, nombre_perfil y status (0 o 1). Los errores se convierten en mensajes.
     *
     * @return never Redirige a /perfiles-profesionales y termina la ejecución.
     */
    public function update(): void
    {
        try {
            (new PerfilProfesionalModel())->guardarPerfil(
                $this->postString('id_perfil', 'perfil'),
                $this->postString('nombre_perfil', 'nombre del perfil', 64),
                $this->postInteger('status', 'estado')
            );
            $this->redirectMessage('/perfiles-profesionales', 'Perfil profesional actualizado correctamente.');
        } catch (\Throwable $error) {
            $this->redirectError('/perfiles-profesionales', $error);
        }
    }
}
