<?php

namespace Modules\FormularioPersona\Controllers;

use App\Controllers\BaseController;
use Modules\PerfilProfesional\Models\PerfilProfesionalModel;
use Modules\FormularioPersona\Models\PersonaModel;

/**
 * Acciones de personas con selección de perfil profesional desde el catálogo.
 */
class Persona extends BaseController
{
    /**
     * @var PersonaModel Modelo de personas inicializado antes de ejecutar las acciones.
     */
    private PersonaModel $personaM;

    /**
     * Prepara el modelo de personas después de que BaseController abra la sesión.
     *
     * @return void Asigna personaM para las acciones del controlador.
     * @throws \Exception Si no se puede obtener la conexión del modelo.
     */
    protected function initialize(): void
    {
        $this->personaM = new PersonaModel();
    }

    /**
     * Muestra las personas activas y los perfiles disponibles para los modales.
     *
     * Entrada de sesión: mensaje y error, que se entregan a la plantilla.
     *
     * @return void Escribe la lista HTML de personas.
     * @throws \Exception Si falla la lectura de datos o la resolución de la vista.
     */
    public function index(): void
    {
        $this->renderModule('listaPersonas', [
            'usuarios' => $this->personaM->getPersonas(),
            'perfiles' => (new PerfilProfesionalModel())->getPerfiles(false),
            'mensaje' => $_SESSION['mensaje'] ?? '', 'error' => $_SESSION['error'] ?? ''
        ]);
    }

    /**
     * Muestra el alta con perfiles activos y recupera una captura previa.
     *
     * Entrada de sesión: persona_anterior, mensaje y error; consume la captura después de renderizar.
     *
     * @return void Escribe el formulario HTML de alta.
     * @throws \Exception Si falla la consulta del catálogo o la resolución de la vista.
     */
    public function viewNewPersona(): void
    {
        $this->renderModule('nuevaPersona', [
            'perfiles' => (new PerfilProfesionalModel())->getPerfiles(),
            'anteriores' => $_SESSION['persona_anterior'] ?? [],
            'mensaje' => $_SESSION['mensaje'] ?? '', 'error' => $_SESSION['error'] ?? ''
        ]);
        unset($_SESSION['persona_anterior']);
    }

    /**
     * Genera un identificador aleatorio para una persona.
     *
     * @param string $acronimo Prefijo utilizado para distinguir los IDs de persona.
     * @return string Identificador con prefijo y sufijo hexadecimal aleatorio.
     * @throws \Random\RandomException Si falla la obtención de bytes aleatorios.
     */
    public function genPersonaId(string $acronimo = 'PER'): string
    {
        return PersonaModel::newId($acronimo);
    }

    /**
     * Lee y valida los datos obligatorios de una persona enviados por POST.
     *
     * Entrada POST: nombre, apellidos, curp, rfc, nivel e id_perfil.
     *
     * @return array{nombre: string, apellido: string, curp: string, rfc: string, nivel_academico: string, id_perfil: string} Datos normalizados para el modelo.
     * @throws \InvalidArgumentException Si un campo obligatorio es inválido o supera su longitud.
     */
    private function readPersona(): array
    {
        return [
            'nombre' => $this->postString('nombre', 'nombre', 64),
            'apellido' => $this->postString('apellidos', 'apellidos', 64),
            'curp' => $this->postString('curp', 'CURP'),
            'rfc' => $this->postString('rfc', 'RFC'),
            'nivel_academico' => $this->postString('nivel', 'nivel académico'),
            'id_perfil' => $this->postString('id_perfil', 'perfil profesional')
        ];
    }

    /**
     * Registra una persona con un perfil activo y un identificador generado.
     *
     * Entrada POST: nombre, apellidos, curp, rfc, nivel e id_perfil.
     * Ante un error conserva los campos de texto en la sesión persona_anterior.
     *
     * @return never Redirige a la lista o al alta con el mensaje correspondiente.
     */
    public function create(): void
    {
        try {
            $data = $this->readPersona() + ['id_persona' => $this->genPersonaId(), 'status' => 1];
            if (!$this->personaM->guardarPersona($data)) {
                throw new \RuntimeException('No se pudo guardar la persona.');
            }
            unset($_SESSION['persona_anterior']);
            $this->redirectMessage('/', 'Persona creada correctamente.');
        } catch (\Throwable $error) {
            $_SESSION['persona_anterior'] = array_filter(
                array_intersect_key($_POST, array_flip(['nombre', 'apellidos', 'curp', 'rfc', 'nivel', 'id_perfil'])),
                'is_string'
            );
            $this->redirectError('/nueva-persona', $error);
        }
    }

    /**
     * Edita los datos de una persona y su relación con un perfil profesional.
     *
     * Entrada POST: user-id, nombre, apellidos, curp, rfc, nivel e id_perfil.
     *
     * @return never Redirige a la lista con la confirmación o el error.
     */
    public function update(): void
    {
        try {
            $this->personaM->updatePersona($this->postString('user-id', 'persona'), $this->readPersona());
            $this->redirectMessage('/', 'Datos de la persona actualizados correctamente.');
        } catch (\Throwable $error) {
            $this->redirectError('/', $error);
        }
    }

    /**
     * Da de baja una persona mediante su estado sin eliminar físicamente sus datos.
     *
     * Entrada POST: user-id de una persona activa.
     *
     * @return never Redirige a la lista con la confirmación o el error.
     */
    public function delete(): void
    {
        try {
            if (!$this->personaM->updateStatus($this->postString('user-id', 'persona'))) {
                throw new \InvalidArgumentException('La persona no existe o ya está inactiva.');
            }
            $this->redirectMessage('/', 'Persona eliminada correctamente.');
        } catch (\Throwable $error) {
            $this->redirectError('/', $error);
        }
    }
}
