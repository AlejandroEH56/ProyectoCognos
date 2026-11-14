<?php

namespace Modules\Evaluaciones\Models;

use App\Models\BaseModel;

/**
 * Consultas compartidas de tests, preguntas y opciones de los módulos de evaluación.
 */
class CuestionarioModel extends BaseModel
{
    /**
     * Busca el primer test del nombre indicado, ordenado por su identificador.
     *
     * @param string $nombre Nombre con el que se registró el test.
     * @param bool $lock Si es true solicita FOR UPDATE; el llamador debe mantener una transacción.
     * @return array{id_test: string, nombre: string} Identificador y nombre del test seleccionado.
     * @throws \InvalidArgumentException Si no está registrado el test.
     * @throws \Exception Si falla la lectura SQL.
     */
    public function getTest(string $nombre, bool $lock = false): array
    {
        $row = $this->queryOne(
            'SELECT id_test, nombre FROM test WHERE nombre = ? ORDER BY id_test LIMIT 1' . ($lock ? ' FOR UPDATE' : ''),
            [$nombre]
        );
        if ($row === null) {
            throw new \InvalidArgumentException('No está registrado el test ' . $nombre . '.');
        }
        return $row;
    }

    /**
     * Obtiene las preguntas y sus opciones, manteniendo las preguntas incompletas.
     *
     * @param string $nombre Nombre del test cuyas opciones se consultan.
     * @return list<array{id_pregunta: string, pregunta: string|null, id_test: string, id_detpregunta: string|null, respuesta: string|null, valor: int|null}> Filas ordenadas por pregunta y opción.
     * @throws \Exception Si falla la consulta.
     */
    protected function getOpciones(string $nombre): array
    {
        return $this->queryPrepared(
            'SELECT p.id_pregunta, p.pregunta, p.id_test, dp.id_detpregunta, dp.respuesta, dp.valor
             FROM pregunta p JOIN test t ON t.id_test = p.id_test
             LEFT JOIN det_pregunta dp ON dp.id_pregunta = p.id_pregunta
             WHERE t.id_test = (SELECT id_test FROM test WHERE nombre = ? ORDER BY id_test LIMIT 1)
             ORDER BY p.id_pregunta, dp.id_detpregunta',
            [$nombre]
        );
    }
}
