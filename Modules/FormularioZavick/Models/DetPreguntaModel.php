<?php

namespace Modules\FormularioZavick\Models;

use App\Models\CuestionarioModel;

class DetPreguntaModel extends CuestionarioModel
{
    protected string $table = 'det_pregunta';
    protected string $primaryKey = 'id_detpregunta';
    protected array $fillable = ['id_detpregunta', 'respuesta', 'id_pregunta', 'valor'];

    public function getEvalTest3(): array
    {
        $preguntas = [];
        foreach ($this->getOpciones('Zavick') as $row) {
            $id = $row['id_pregunta'];
            if (!isset($preguntas[$id])) {
                $preguntas[$id] = ['id_pregunta' => $id, 'pregunta' => $row['pregunta'], 'opciones' => []];
            }
            if ($row['id_detpregunta'] !== null) {
                $preguntas[$id]['opciones'][] = [
                    'idRes' => $row['id_detpregunta'], 'campo' => $row['respuesta'], 'valor' => $row['valor']
                ];
            }
        }
        return array_values($preguntas);
    }

    public function getRespuestaCount(): array
    {
        return $this->queryPrepared('SELECT COUNT(*) AS total FROM det_pregunta')[0];
    }

    private function lockQuestion(string $id): array
    {
        $test = $this->getTest('Zavick', true);
        $rows = $this->queryPrepared(
            'SELECT id_pregunta FROM pregunta WHERE id_pregunta = ? AND id_test = ? FOR UPDATE',
            [$id, $test['id_test']]
        );
        if (!$rows) {
            throw new \InvalidArgumentException('La pregunta no pertenece al test Zavick.');
        }
        return array_column($this->queryPrepared(
            'SELECT id_detpregunta FROM det_pregunta WHERE id_pregunta = ? ORDER BY id_detpregunta FOR UPDATE', [$id]
        ), 'id_detpregunta');
    }

    public function createQuestion(array $pregunta, array $respuestas): void
    {
        $this->transaction(function () use ($pregunta, $respuestas) {
            $test = $this->getTest('Zavick', true);
            $pregunta['id_test'] = $test['id_test'];
            if (!(new PreguntaModel())->createPregunta($pregunta)) {
                throw new \RuntimeException('No se pudo crear la pregunta.');
            }
            $this->createDetPreguntas($pregunta['id_pregunta'], $respuestas);
        });
    }

    public function updatePregunta($id, $descripcion, $respuestas): bool
    {
        return $this->transaction(function () use ($id, $descripcion, $respuestas) {
            $existing = $this->lockQuestion($id);
            $ids = array_column($respuestas, 0);
            if (count($ids) !== 4 || count(array_unique($ids)) !== 4 || count($existing) !== 4
                || array_diff($existing, $ids)) {
                throw new \InvalidArgumentException('Las cuatro opciones deben pertenecer a la pregunta indicada.');
            }
            $this->executePrepared('UPDATE pregunta SET pregunta = ? WHERE id_pregunta = ?', [$descripcion, $id]);
            foreach ($respuestas as $respuesta) {
                $this->executePrepared(
                    'UPDATE det_pregunta SET respuesta = ?, valor = ? WHERE id_detpregunta = ? AND id_pregunta = ?',
                    [$respuesta[1], $respuesta[2], $respuesta[0], $id]
                );
            }
            return true;
        });
    }

    public function createDetPreguntas(string $id, array $respuestas): bool
    {
        return $this->transaction(function () use ($id, $respuestas) {
            if (count($respuestas) !== 4) {
                throw new \InvalidArgumentException('La pregunta debe tener cuatro opciones.');
            }
            foreach ($respuestas as $respuesta) {
                if (!$this->create([
                    'id_detpregunta' => $respuesta[0], 'respuesta' => $respuesta[1],
                    'id_pregunta' => $id, 'valor' => $respuesta[2]
                ])) {
                    throw new \RuntimeException('No se pudo guardar una de las opciones.');
                }
            }
            return true;
        });
    }

    public function deleteDetPreguntas(array $respuestas): bool
    {
        return $this->transaction(function () use ($respuestas) {
            foreach ($respuestas as $id) {
                if (!$this->delete($id)) {
                    throw new \InvalidArgumentException('No se encontró una de las opciones para eliminar.');
                }
            }
            return true;
        });
    }

    public function deleteQuestion(string $id, array $ids = []): void
    {
        $this->transaction(function () use ($id, $ids) {
            $existing = $this->lockQuestion($id);
            if ($ids && (count($ids) !== count($existing) || array_diff($ids, $existing)
                || count(array_unique($ids)) !== count($ids))) {
                throw new \InvalidArgumentException('Las opciones para eliminar no coinciden con la pregunta.');
            }
            $this->deleteDetPreguntas($existing);
            if (!(new PreguntaModel())->deletePregunta($id)) {
                throw new \RuntimeException('No se pudo eliminar la pregunta.');
            }
        });
    }
}
