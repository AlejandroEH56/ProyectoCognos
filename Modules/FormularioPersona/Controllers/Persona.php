<?php

namespace Modules\FormularioPersona\Controllers;


use App\Controllers\BaseController;
use Modules\FormularioPersona\Models\PersonaModel;

class Persona extends BaseController
{
    private $personaM;

    public function __construct()
    {
        $this->personaM = new PersonaModel();
    }

    /**
     * Vista principal de la lista de personas
     * @return void
     */
    public function index()
    {
        $personas = $this->personaM->getPersonas();
        $mensaje = $_SESSION['mensaje'] ?? '';
        $error = $_SESSION['error'] ?? '';
        $this->renderModule('listaPersonas', [
            'usuarios' => $personas,
            'mensaje' => $mensaje,
            'error' => $error
        ]);
    }

    /**
     * Devuelve la vista del formulario para crear una nueva persona
     * @return void
     */
    public function viewNewPersona()
    {

        $mensaje = $_SESSION['mensaje'] ?? '';
        $error = $_SESSION['error'] ?? '';
        $this->renderModule('nuevaPersona', [
            'mensaje' => $mensaje,
            'error' => $error
        ]);
    }

    /**
     * Genera el ID de la Persona (crear persona)
     * @param string $acronimo
     * @return string
     */
    public function genPersonaId(string $acronimo = 'PER'): string
    {
        $numeroConsecutivo = $this->personaM->getPersonaCount();
        // Formatear número con ceros a la izquierda (mínimo 3 dígitos) y formatear fecha y hora
        $numeroFormateado = str_pad($numeroConsecutivo['total'] + 1, 3, '0', STR_PAD_LEFT);
        $fechaHora = date('dmYHis');

        // return ($acronimo ?? 'PER') . '-' . $numeroFormateado . '-' . $fechaHora;
        return "{$acronimo}-{$numeroFormateado}-{$fechaHora}";
    }

    /**
     * Crea una nueva persona
     * @return null
     */
    public function create()
    {
        if (!isset($_POST['nombre']) || !isset($_POST['nivel'])) {
            $_SESSION['error'] = "Titulo y/o Contenido no proporcionado";
            header("Location: /");
            return exit();
        }

        $data = [
            'id_persona'    => self::genPersonaId(),
            'nombre'        => $_POST['nombre'],
            'apellido'      => $_POST['apellidos'],
            'curp'          => $_POST['curp'],
            'rfc'           => $_POST['rfc'],
            'nivel_academico'       => $_POST['nivel'],
            'perfil_profesional'    => $_POST['perfil'],
            'id_perfil'     => null,
            'status'        => 1,
        ];

        if (!$this->personaM->guardarPersona($data)) {
            $_SESSION['error'] = "Error al guardar la persona.";
            header("Location: /");
            return exit();
        }
        // Redirige o muestra un mensaje de éxito
        $_SESSION['mensaje'] = "Persona creada con exito.";
        header("Location: /");
        return exit();
    }

    /**
     * Actualiza los datos de una persona
     * @return null
     */
    public function update()
    {
        if (!isset($_POST['user-id'])) {
            $_SESSION['error'] = "ID de persona no proporcionado";
            header("Location: /");
            return exit();
        }

        $data = [
            'nombre'        => $_POST['nombre'],
            'apellido'      => $_POST['apellidos'],
            'curp'          => $_POST['curp'],
            'rfc'           => $_POST['rfc'],
            'nivel_academico'       => $_POST['nivel'],
            'perfil_profesional'    => $_POST['perfil'],
        ];

        if (!$this->personaM->updatePersona($_POST['user-id'], $data)) {
            $_SESSION['error'] = "Error al eliminar la persona.";
            header("Location: /");
            return exit();
        }

        $_SESSION['mensaje'] = "Datos de la persona actualizados correctamente.";
        header("Location: /");
        return exit();
    }

    /**
     * Elimina una persona
     * @return null
     */
    public function delete()
    {
        if (!isset($_POST['user-id'])) {
            $_SESSION['error'] = "ID de persona no proporcionado";
            header("Location: /");
            return exit();
        }

        if (!$this->personaM->updateStatus($_POST['user-id'])) {
            $_SESSION['error'] = "Error al eliminar la persona.";
            header("Location: /");
            return exit();
        }

        $_SESSION['mensaje'] = "Persona eliminada correctamente.";
        header("Location: /");
        return exit();
    }
}
