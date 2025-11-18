<?php

namespace Modules\FormularioKostick\Models;

use App\Models\BaseModel;

class DetPreguntaModel extends BaseModel
{
    protected string $table = 'db_cognos.det_pregunta';
    protected string $primaryKey = 'id_detpregunta';
    protected array $fillable = ['id_detpregunta', 'respuesta', 'id_pregunta', 'valor'];

    /**
     * Devuelve las respuestas del Test 2 (Kostick)
     * @return array
     */
    public function getEvalTest2(): array
    {
        $result = $this->db->query(
            "SELECT id_detpregunta, det_pregunta.respuesta, det_pregunta.valor
	                FROM db_cognos.det_pregunta
                    JOIN db_cognos.pregunta ON pregunta.id_pregunta = det_pregunta.id_pregunta
                    JOIN db_cognos.test ON pregunta.id_test = test.id_test
                    WHERE test.nombre = 'Kostick';"
        )->fetch_all(MYSQLI_ASSOC);

        return $result;
    }

    /**
     * Crea una pregunta
     * @param array $detPregunta Datos de la Pregunta/Respuesta
     * @return bool true si se insertan los datos correctamente
     */
    public function createDetPregunta(array $dataPregunta): bool
    {
        // Desempaquetar valores del array (asegurar orden correcto)
        $data = [
            'id_detpregunta' => $dataPregunta['id_detpregunta'],
            'respuesta' => $dataPregunta['respuesta'],
            'id_pregunta' => $dataPregunta['id_pregunta'],
            'valor' => $dataPregunta['valor']
        ];

        return $this->create($data);
    }

    /**
     * Actualiza los detalles de una pregunta
     * @param string $id Id_detPregunta
     * @param array $dataPregunta
     * @return int
     */
    public function updateDetPregunta(string $id, array $dataPregunta): int
    {
        $data = [$dataPregunta['respuesta'], $dataPregunta['valor'], $id];
        $result = $this->executePrepared("UPDATE det_pregunta SET respuesta = ?, valor = ? WHERE id_detpregunta = ?", $data);
        return $result;
    }

    /**
     * Elimina una pregunta
     * @param string $id Id_detPregunta
     * @return bool
     */
    public function deleteDetPregunta(string $id): bool
    {
        return $this->delete($id);
    }


    /**
     * Devuelve el total de preguntas (creacion de preguntas)
     * @return array|array{total: int|bool|null}
     */
    public function getRespuestaCount(): array
    {
        $result = $this->db->query("SELECT COUNT(*) AS total FROM db_cognos.det_pregunta");
        $count = $result ? $result->fetch_assoc() : ['total' => 0];

        return $count;
    }

    /**
     * Devuelve el id_pregunta del test kostick (creacion de preguntas)
     * @return array
     */
    public function getIDPreguntaKostick(): array
    {
        $result = $this->db->query(
            "SELECT det_pregunta.id_pregunta AS ID
	                FROM db_cognos.det_pregunta
                    JOIN db_cognos.pregunta ON pregunta.id_pregunta = det_pregunta.id_pregunta 
                    JOIN db_cognos.test ON pregunta.id_test = test.id_test
                    WHERE test.nombre = 'kostick' limit 1"
        );
        $count = $result ? $result->fetch_assoc() : ['ID' => null];
        return $count;
    }
}
