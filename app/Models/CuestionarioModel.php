<?php

namespace App\Models;

class CuestionarioModel extends BaseModel
{
    public function getTest(string $nombre, bool $lock = false): array
    {
        $rows = $this->queryPrepared(
            'SELECT id_test, nombre FROM test WHERE nombre = ? ORDER BY id_test LIMIT 1' . ($lock ? ' FOR UPDATE' : ''),
            [$nombre]
        );
        if (!$rows) {
            throw new \InvalidArgumentException('No está registrado el test ' . $nombre . '.');
        }
        return $rows[0];
    }

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
