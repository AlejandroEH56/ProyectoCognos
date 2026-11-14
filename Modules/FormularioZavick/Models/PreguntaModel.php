<?php

namespace Modules\FormularioZavick\Models;

use Modules\Evaluaciones\Models\CuestionarioModel;

/**
 * Persistencia de encabezados de pregunta del cuestionario Zavick.
 */
class PreguntaModel extends CuestionarioModel
{
    /**
     * @var string Tabla de encabezados de pregunta.
     */
    protected string $table = 'pregunta';
    /**
     * @var string Identificador único de una pregunta.
     */
    protected string $primaryKey = 'id_pregunta';
    /**
     * @var list<string> Campos permitidos para guardar encabezados de pregunta.
     */
    protected array $fillable = ['id_pregunta', 'pregunta', 'tipo_respuesta', 'id_test'];


    /**
     * Inserta un encabezado con el test previamente asignado por el llamador.
     *
     * @param array{id_pregunta: string, pregunta: string, tipo_respuesta: int, id_test: string} $dataPregunta Datos completos del encabezado.
     * @return bool True si se insertó una fila; false si no se insertaron filas.
     * @throws \Exception Si falla la persistencia.
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
     * Resuelve el test registrado con el nombre Zavick.
     *
     * @return array{ID: string} Identificador del test bajo la clave ID.
     * @throws \InvalidArgumentException Si no está registrado el test.
     * @throws \Exception Si falla la lectura.
     */
    public function getIDTest()
    {
        return ['ID' => $this->getTest('Zavick')['id_test']];
    }


    /**
     * Cuenta todos los encabezados de pregunta, de cualquier test.
     *
     * @return array{total: int} Total de registros de la tabla pregunta.
     * @throws \Exception Si falla el conteo.
     */
    public function getPreguntaCount(): array
    {
        return ['total' => $this->count()];
    }

    /**
     * Elimina físicamente un encabezado por su identificador.
     *
     * @param string $id Identificador de la pregunta a eliminar.
     * @return bool True si eliminó una fila; false si no existe el ID.
     * @throws \Exception Si falla la baja o existen registros relacionados que la impiden.
     */
    public function deletePregunta(string $id): bool
    {
        return $this->delete($id);
    }
}
