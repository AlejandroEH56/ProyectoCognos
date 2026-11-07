<?php

namespace Modules\FormularioKostick\Models;

use App\Models\CuestionarioModel;

class DetPreguntaModel extends CuestionarioModel
{
    protected string $table = 'det_pregunta';
    protected string $primaryKey = 'id_detpregunta';
    protected array $fillable = ['id_detpregunta', 'respuesta', 'id_pregunta', 'valor'];

    /**
     * Devuelve las respuestas del Test 2 (Kostick)
     * @return array
     */
    public function getEvalTest2(): array
    {
        return array_values(array_filter($this->getOpciones('Kostick'), fn($row) => $row['id_detpregunta'] !== null));
    }

    /**
     * Crea una pregunta
     * @param array $dataPregunta Datos de la Pregunta/Respuesta
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
        $result = $this->db->query("SELECT COUNT(*) AS total FROM det_pregunta");
        $count = $result ? $result->fetch_assoc() : ['total' => 0];

        return $count;
    }

    /**
     * Devuelve el id_pregunta del test kostick (creacion de preguntas)
     * @return array
     */
    public function getIDPreguntaKostick(): array
    {
        $rows = $this->queryPrepared(
            'SELECT p.id_pregunta AS ID FROM pregunta p JOIN test t ON t.id_test = p.id_test
             WHERE t.id_test = (SELECT id_test FROM test WHERE nombre = ? ORDER BY id_test LIMIT 1)
             ORDER BY p.id_pregunta LIMIT 1', ['Kostick']
        );
        return $rows[0] ?? ['ID' => null];
    }

    public function createPair(array $opciones): void
    {
        $this->transaction(function () use ($opciones) {
            if (count($opciones) !== 2) {
                throw new \InvalidArgumentException('Se requieren dos opciones para crear un par.');
            }
            $test = $this->getTest('Kostick', true);
            $id = $this->getIDPreguntaKostick()['ID'];
            if ($id === null) {
                $id = self::newId('preg');
                $this->executePrepared(
                    'INSERT INTO pregunta (id_pregunta, pregunta, tipo_respuesta, id_test) VALUES (?, ?, ?, ?)',
                    [$id, 'Elige la frase que más te describe', 1, $test['id_test']]
                );
            }
            // El prefijo mantiene los pares nuevos juntos, después de los IDs consecutivos anteriores.
            $pairId = self::newId('dpreg-par');
            foreach ($opciones as $index => $opcion) {
                if (!$this->createDetPregunta([
                    'id_detpregunta' => $pairId . '-' . ($index + 1),
                    'respuesta' => $opcion['respuesta'], 'valor' => $opcion['valor'], 'id_pregunta' => $id
                ])) {
                    throw new \RuntimeException('No se pudo crear el par de opciones.');
                }
            }
        });
    }

    private function lockPair(array $ids): void
    {
        $this->getTest('Kostick', true);
        $rows = $this->getEvalTest2();
        for ($i = 0; $i + 1 < count($rows); $i += 2) {
            $pair = [$rows[$i]['id_detpregunta'], $rows[$i + 1]['id_detpregunta']];
            if (count($ids) === 2 && count(array_unique($ids)) === 2 && !array_diff($ids, $pair)
                && $rows[$i]['id_pregunta'] === $rows[$i + 1]['id_pregunta']) {
                return;
            }
        }
        throw new \InvalidArgumentException('Las opciones no corresponden a un par vigente de Kostick.');
    }

    public function updatePair(array $ids, array $opciones): void
    {
        $this->transaction(function () use ($ids, $opciones) {
            $this->lockPair($ids);
            foreach ($ids as $index => $id) {
                $this->updateDetPregunta($id, $opciones[$index]);
            }
        });
    }

    public function deletePair(array $ids): void
    {
        $this->transaction(function () use ($ids) {
            $this->lockPair($ids);
            foreach ($ids as $id) {
                if (!$this->deleteDetPregunta($id)) {
                    throw new \RuntimeException('No se pudo eliminar el par de opciones.');
                }
            }
        });
    }
}
