<?php

namespace Modules\FormularioZavick\Models;

use App\Models\BaseModel;

class PreguntaModel extends BaseModel
{
    protected string $table = 'db_cognos.pregunta';
    protected string $primaryKey = 'id_pregunta';
    protected array $fillable = ['id_pregunta', 'pregunta', 'tipo_respuesta', 'id_test'];


    /**
     * Crea una pregunta
     * @param array $detPregunta Datos de la Pregunta/Respuesta
     * @return bool true si se insertan los datos correctamente, false en caso contrario.
     */
    public function createPregunta(array $dataPregunta): bool
    {
        // Desempaquetar valores del array (asegurar orden correcto)
        $data = [
            'id_pregunta'    => $dataPregunta['id_pregunta'],
            'pregunta'       => $dataPregunta['pregunta'],
            'tipo_respuesta' => $dataPregunta['tipo_respuesta'],
            'id_test'        => $dataPregunta['id_test'],
        ];
        return $this->create($data);
    }

    /**
     * Devuelve el ID del test (Zavick)
     * @return array
     */
    public function getIDTest()
    {
        $result = $this->queryPrepared("SELECT id_test AS ID FROM db_cognos.test WHERE test.nombre = ?;", ['zavick']);
        return $result[0];
    }


    /**
     * Devuelve el total de preguntas
     * @return array|array{total: int|bool|null}
     */
    public function getPreguntaCount(): array
    {
        $result = $this->db->query("SELECT COUNT(*) AS total FROM db_cognos.pregunta");
        $count = $result ? $result->fetch_assoc() : ['total' => 0];

        return $count;
    }

    /**
     * Elimina una pregunta 
     * @param string $id Id_Pregunta
     * @return bool true si se elimina, false en caso contrario
     */
    public function deletePregunta(string $id): bool
    {
        return $this->delete($id);
    }
}
