<?php

namespace Modules\FormularioZavick\Models;

use App\Models\BaseModel;

class DetPreguntaModel extends BaseModel
{
    protected string $table = 'db_cognos.det_pregunta';
    protected string $primaryKey = 'id_detpregunta';
    protected array $fillable = ['id_detpregunta', 'respuesta', 'id_pregunta', 'valor'];

    /**
     * Devuelve las Preguntas y respuestas del Test 3 (Zavic)
     * @return array
     */
    public function getEvalTest3(): array
    {
        $result = $this->db->query(
            "SELECT p.id_pregunta, p.pregunta, 
                    GROUP_CONCAT(dp.respuesta SEPARATOR '/') AS opciones, 
                    GROUP_CONCAT(dp.valor SEPARATOR '/') AS valores, 
                    GROUP_CONCAT(dp.id_detpregunta SEPARATOR '/') AS identificadores
                    FROM db_cognos.pregunta AS p 
                    JOIN db_cognos.det_pregunta AS dp ON dp.id_pregunta = p.id_pregunta
                    JOIN db_cognos.test AS t ON p.id_test = t.id_test
                    WHERE t.nombre = 'Zavick'
                    GROUP BY p.id_pregunta, p.pregunta;"
        )->fetch_all(MYSQLI_ASSOC);

        return $result;
    }

    /**
     * Devuelve el total de det preguntas
     * @return array|array{total: int|bool|null}
     */
    public function getRespuestaCount(): array
    {
        $result = $this->db->query("SELECT COUNT(*) AS total FROM db_cognos.det_pregunta");
        $count = $result ? $result->fetch_assoc() : ['total' => 0];

        return $count;
    }


    /**
     * Actualiza la pregunta y sus respuestas en la base de datos.
     * Solo realiza UPDATE; no inserta nuevos registros.
     * @param int $id ID de la pregunta (id_pregunta).
     * @param string $descripcion Descripción de la pregunta (campo 'pregunta').
     * @param array $respuestas Array de respuestas: [[id_detpregunta, respuesta, valor], ...].
     * @throws \Exception Si ocurre un error en la actualización.
     * @return bool Retorna true si se guardaron corectamente
     */
    public function updatePregunta($id, $descripcion, $respuestas): bool
    {
        try {
            // Iniciar transacción para atomicidad
            $this->db->begin_transaction();
            // Actualizar la pregunta en tabla 'pregunta'
            $result = $this->executePrepared("UPDATE pregunta SET pregunta = ? WHERE id_pregunta = ?", [$descripcion, $id]);

            if ($result == 0) {
                throw new \Exception("No se encontró la pregunta con ID: $id");
            }
            // Actualizar cada respuesta en tabla 'det_pregunta'
            foreach ($respuestas as $respuesta) {
                $result = $this->executePrepared("UPDATE det_pregunta SET respuesta = ?, valor = ? WHERE id_detpregunta = ?", [$respuesta[1], $respuesta[2], $respuesta[0]]);

                if ($result == 0) {
                    throw new \Exception("No se encontró la respuesta con ID: {$respuesta[0]}");
                }
            }
            // Confirmar transacción
            $this->db->commit();
            return true;
        } catch (\Exception $e) {
            $this->db->rollBack();
            throw new \Exception("Error al actualizar: " . $e->getMessage());
        }
    }

    /**
     * Guarda una nueva pregunta zavick
     * @param string $id ID Pregunta
     * @param array $respuestas Arreglo con las respuestas de la pregunta
     * @return bool Devuelve true en caso exito
     * @throws \Exception
     */
    public function createDetPreguntas(string $id, array $respuestas): bool
    {
        try {
            // Iniciar transacción
            $this->db->begin_transaction();
            // Para cada respuesta, crear/insertar
            foreach ($respuestas as $respuesta) {
                $result = $this->create(['id_detpregunta' => $respuesta[0], 'respuesta' => $respuesta[1], 'id_pregunta' => $id, 'valor' => $respuesta[2]]);
                if (!$result) {
                    throw new \Exception("No se insertaron los datos correctamente.");
                }
            }
            $this->db->commit();
            return true;  // Éxito
        } catch (\Exception $e) {
            // Revertir en caso de error
            $this->db->rollBack();
            throw new \Exception("Error al guardar: " . $e->getMessage());
        }
    }

    /**
     * Elimina un grupo de preguntas (zavick)
     * @param array $respuestas Arreglo con IDs de las respuestas
     * @return bool Devuelve true en caso exito
     * @throws \Exception
     */
    public function deleteDetPreguntas(array $respuestas): bool
    {
        try {
            $this->db->begin_transaction();
            // Para cada respuesta, eliminar
            foreach ($respuestas as $respuesta) {
                $result = $this->delete($respuesta);
                if (!$result) {
                    throw new \Exception("No se elimino la respuesta correctamente: {$respuesta}");
                }
            }
            $this->db->commit();
            return true; // Éxito
        } catch (\Exception $e) {
            // Revertir en caso de error
            $this->db->rollBack();
            throw new \Exception("Error: " . $e->getMessage());
        }
    }
}
