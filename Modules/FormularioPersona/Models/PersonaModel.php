<?php

namespace Modules\FormularioPersona\Models;

use App\Models\BaseModel;
use Modules\PerfilProfesional\Models\PerfilProfesionalModel;

/**
 * Persistencia de personas y validación de su relación con perfiles profesionales.
 */
class PersonaModel extends BaseModel
{
    /**
     * @var string Tabla de personas en la conexión configurada.
     */
    protected string $table = 'persona';
    /**
     * @var string Clave primaria de una persona.
     */
    protected string $primaryKey = 'id_persona';
    /**
     * @var list<string> Columnas de persona permitidas para altas y modificaciones.
     */
    protected array $fillable = ['id_persona', 'nombre', 'apellido', 'curp', 'rfc', 'nivel_academico', 'perfil_profesional', 'id_perfil', 'status'];

    /**
     * Lista personas activas con el nombre actual del perfil y su estado.
     *
     * Conserva como referencia el texto antiguo cuando falta un nombre de catálogo.
     *
     * @return list<array{ID: string, nombre: string|null, apellido: string|null, curp: string|null, rfc: string|null, nivel_academico: string|null, perfil_profesional: string, id_perfil: string|null, perfil_status: int|null}> Filas para la lista y sus modales.
     * @throws \Exception Si falla la consulta.
     */
    public function getPersonas(): array
    {
        return $this->queryPrepared(
            "SELECT p.id_persona AS ID, p.nombre, p.apellido, p.curp, p.rfc, p.nivel_academico,
                    COALESCE(NULLIF(pp.nombre_perfil, ''), p.perfil_profesional, 'Sin perfil asignado') AS perfil_profesional,
                    p.id_perfil, pp.status AS perfil_status
             FROM persona p LEFT JOIN perfil_profesional pp ON pp.id_perfil = p.id_perfil
             WHERE p.status = 1 ORDER BY p.nombre, p.apellido, p.id_persona"
        );
    }

    /**
     * Valida y bloquea el perfil seleccionado y deriva su nombre de compatibilidad.
     *
     * Debe ejecutarse dentro de una transacción. Un perfil inactivo solo puede conservarse
     * cuando coincide con la asignación anterior de la persona.
     *
     * @param array<string, mixed> $data Datos de persona que incluyen id_perfil.
     * @param string|null $perfilAnterior ID ya asignado, o null durante una nueva alta.
     * @return array<string, mixed> Datos de entrada con perfil_profesional obtenido del catálogo.
     * @throws \InvalidArgumentException Si falta el perfil o la nueva asignación no es válida.
     * @throws \LogicException Si se solicita el bloqueo fuera de una transacción.
     * @throws \Exception Si falla la consulta del perfil.
     */
    private function withPerfil(array $data, ?string $perfilAnterior = null): array
    {
        $id = $data['id_perfil'] ?? null;
        if (!is_string($id) || trim($id) === '') {
            throw new \InvalidArgumentException('Selecciona un perfil profesional.');
        }
        $perfil = (new PerfilProfesionalModel())->getPerfil($id, true);
        if ($perfil === null || ((int)$perfil['status'] !== 1 && $id !== $perfilAnterior)) {
            throw new \InvalidArgumentException('Selecciona un perfil profesional activo.');
        }
        // Compatibilidad con la columna de texto antigua; las lecturas usan la relación.
        $data['perfil_profesional'] = $perfil['nombre_perfil'];
        return $data;
    }

    /**
     * Inserta una persona y valida su perfil dentro de la misma transacción.
     *
     * @param array{id_persona: string, nombre: string, apellido: string, curp: string, rfc: string, nivel_academico: string, id_perfil: string, status: int, perfil_profesional?: string|null} $personaInfo Datos de alta; el nombre del perfil se deriva del catálogo.
     * @return bool True si se insertó la persona; false si no se insertaron filas.
     * @throws \InvalidArgumentException Si no hay un perfil activo válido.
     * @throws \Exception Si falla la escritura; revierte la operación.
     */
    public function guardarPersona(array $personaInfo = []): bool
    {
        return $this->transaction(function () use ($personaInfo) {
            $data = $this->withPerfil($personaInfo);
            return $this->create($data);
        });
    }

    /**
     * Cuenta todas las personas, incluidas las inactivas.
     *
     * @return array{total: int} Total de registros de la tabla persona.
     * @throws \Exception Si falla el conteo.
     */
    public function getPersonaCount(): array
    {
        return ['total' => $this->count()];
    }

    /**
     * Da de baja una persona activa actualizando status a cero.
     *
     * @param string $id Identificador de la persona que se desea dar de baja.
     * @return bool True si se cambió el estado; false si no existe o ya estaba inactiva.
     * @throws \Exception Si falla la escritura SQL.
     */
    public function updateStatus(string $id): bool
    {
        return $this->executePrepared('UPDATE persona SET status = 0 WHERE id_persona = ? AND status = 1', [$id]) > 0;
    }

    /**
     * Actualiza una persona activa y valida su asignación de perfil de forma atómica.
     *
     * @param string $id Identificador de la persona existente.
     * @param array{nombre: string, apellido: string, curp: string, rfc: string, nivel_academico: string, id_perfil: string} $infoPersona Datos editables; el nombre del perfil se obtiene del catálogo.
     * @return bool True tras guardar, incluidos valores idénticos; false si la escritura no encuentra la fila.
     * @throws \InvalidArgumentException Si la persona está ausente/inactiva o el perfil no es admisible.
     * @throws \Exception Si falla la persistencia; se revierten las escrituras.
     */
    public function updatePersona(string $id, array $infoPersona): bool
    {
        return $this->transaction(function () use ($id, $infoPersona) {
            $existing = $this->find($id, true);
            if ($existing === null || (int)$existing['status'] !== 1) {
                throw new \InvalidArgumentException('La persona no existe o está inactiva.');
            }
            $data = $this->withPerfil($infoPersona, $existing['id_perfil']);
            return parent::update($id, array_intersect_key($data, array_flip([
                'nombre', 'apellido', 'curp', 'rfc', 'nivel_academico', 'perfil_profesional', 'id_perfil'
            ])));
        });
    }
}
