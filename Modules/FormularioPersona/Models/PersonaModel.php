<?php

namespace Modules\FormularioPersona\Models;

use App\Models\BaseModel;

class PersonaModel extends BaseModel
{
    protected string $table = 'db_cognos.persona';
    protected string $primaryKey = 'id_persona';
    protected array $fillable = ['id_persona', 'nombre', 'apellido', 'curp', 'rfc', 'nivel_academico', 'perfil_profesional', 'id_perfil', 'status'];

    /**
     * Devuelve la lista de personas activas
     * @return array
     */
    public function getPersonas(): array
    {
        $result = $this->queryPrepared(
            "SELECT id_persona AS ID, nombre, apellido, curp, rfc, nivel_academico, perfil_profesional, id_perfil FROM db_cognos.persona WHERE persona.status = 1"
        );

        return $result;
    }

    /**
     * Guarda los datos de una nueva persona
     * @param array $personaInfo
     * @return bool
     */
    public function guardarPersona(array $personaInfo = []): bool
    {
        if (empty($personaInfo)) {
            return false;
        }

        // Asegurando el orden en los campos
        $data = [
            $personaInfo['id_persona'],
            $personaInfo['nombre'],
            $personaInfo['apellido'],
            $personaInfo['curp'],
            $personaInfo['rfc'],
            $personaInfo['nivel_academico'],
            $personaInfo['perfil_profesional'],
            $personaInfo['id_perfil'],
            $personaInfo['status'],
        ];

        $sql = "INSERT INTO db_cognos.persona (id_persona, nombre, apellido, curp, rfc, nivel_academico, perfil_profesional, id_perfil, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $result = $this->executePrepared($sql, $data);

        if (!($result > 0)) {
            return false;
        }

        return true;
    }

    /**
     * Devuelve la cuenta del total de personas (suma total)
     * @return array|array{total: int|bool|null}
     */
    public function getPersonaCount(): array
    {
        $result = $this->db->query("SELECT COUNT(*) AS total FROM db_cognos.persona;");
        $count = $result ? $result->fetch_assoc() : ['total' => 0];

        return $count;
    }

    /**
     * Modifica el estado de la persona (Eliminar)
     * @return bool
     */
    public function updateStatus(string $id): bool
    {
        $result = $this->executePrepared("UPDATE persona SET status = 0 WHERE id_persona = ?", [$id]);
        return $result;
    }

    /**
     * Actualiza los datos de la persona
     * @param string $id ID de la persona
     * @param array $infoPersona Datos de la persona
     * @return bool
     */
    public function updatePersona(string $id, array $infoPersona): bool
    {
        // Asegurando el orden en los campos
        $data = [
            $infoPersona['nombre'],
            $infoPersona['apellido'],
            $infoPersona['curp'],
            $infoPersona['rfc'],
            $infoPersona['nivel_academico'],
            $infoPersona['perfil_profesional'],
            $id,
        ];

        $result = $this->executePrepared("UPDATE persona SET nombre = ?, apellido = ?, curp = ?, rfc = ?, nivel_academico = ?, perfil_profesional = ? WHERE id_persona = ?", $data);

        return $result;
    }
}
