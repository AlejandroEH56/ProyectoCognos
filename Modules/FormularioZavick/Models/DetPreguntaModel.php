<?php

namespace Modules\FormularioZavick\Models;

use Modules\Evaluaciones\Models\CuestionarioModel;

/**
 * Preguntas y opciones de Zavick con validación de pertenencia y escrituras atómicas.
 */
class DetPreguntaModel extends CuestionarioModel
{
    /**
     * @var string Tabla compartida de opciones de preguntas.
     */
    protected string $table = 'det_pregunta';
    /**
     * @var string Clave primaria de cada opción.
     */
    protected string $primaryKey = 'id_detpregunta';
    /**
     * @var list<string> Campos que se pueden insertar o actualizar en las opciones.
     */
    protected array $fillable = ['id_detpregunta', 'respuesta', 'id_pregunta', 'valor'];

    /**
     * Agrupa las filas del catálogo en preguntas con sus opciones ordenadas.
     *
     * @return list<array{id_pregunta: string, pregunta: string|null, opciones: list<array{idRes: string, campo: string|null, valor: int|null}>}> Preguntas; opciones puede estar vacío en registros antiguos.
     * @throws \Exception Si falla la lectura del catálogo.
     */
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

    /**
     * Cuenta todas las opciones de la tabla, sin limitar por test.
     *
     * @return array{total: int} Total de registros de det_pregunta.
     * @throws \Exception Si falla el conteo.
     */
    public function getRespuestaCount(): array
    {
        return ['total' => $this->count()];
    }

    /**
     * Bloquea y valida una pregunta de Zavick junto con sus opciones existentes.
     *
     * El llamador debe mantener una transacción activa.
     *
     * @param string $id Identificador de la pregunta a comprobar.
     * @return list<string> IDs de las opciones que pertenecen actualmente a la pregunta.
     * @throws \InvalidArgumentException Si la pregunta no pertenece al test registrado.
     * @throws \LogicException Si no hay una transacción para bloquear el registro.
     * @throws \Exception Si falla la lectura o el bloqueo.
     */
    private function lockQuestion(string $id): array
    {
        $test = $this->getTest('Zavick', true);
        $pregunta = (new PreguntaModel())->find($id, true);
        if ($pregunta === null || $pregunta['id_test'] !== $test['id_test']) {
            throw new \InvalidArgumentException('La pregunta no pertenece al test Zavick.');
        }
        return array_column($this->queryPrepared(
            'SELECT id_detpregunta FROM det_pregunta WHERE id_pregunta = ? ORDER BY id_detpregunta FOR UPDATE', [$id]
        ), 'id_detpregunta');
    }

    /**
     * Inserta el encabezado y las cuatro opciones de una pregunta de forma atómica.
     *
     * @param array{id_pregunta: string, pregunta: string, tipo_respuesta: int} $pregunta Encabezado validado; el ID del test se obtiene del catálogo.
     * @param list<array{0: string, 1: string, 2: int}> $respuestas Cuatro tuplas de ID de opción, descripción y valor.
     * @return void Confirma el encabezado y sus opciones en una sola transacción.
     * @throws \InvalidArgumentException Si falta el test o no se reciben cuatro opciones.
     * @throws \Exception Si falla una escritura; revierte la pregunta completa.
     */
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

    /**
     * Actualiza un encabezado y sus cuatro opciones verificando su pertenencia.
     *
     * @param string $id Identificador de una pregunta vigente de Zavick.
     * @param string $descripcion Texto validado del encabezado.
     * @param list<array{0: string, 1: string, 2: int}> $respuestas Cuatro opciones existentes, con sus descripciones y valores nuevos.
     * @return bool True si la operación completa termina correctamente, incluso sin cambios.
     * @throws \InvalidArgumentException Si la pregunta o los IDs de opciones no corresponden.
     * @throws \Exception Si falla una escritura; revierte toda la edición.
     */
    public function updatePregunta($id, $descripcion, $respuestas): bool
    {
        return $this->transaction(function () use ($id, $descripcion, $respuestas) {
            $existing = $this->lockQuestion($id);
            $ids = array_column($respuestas, 0);
            if (count($ids) !== 4 || count(array_unique($ids)) !== 4 || count($existing) !== 4
                || array_diff($existing, $ids)) {
                throw new \InvalidArgumentException('Las cuatro opciones deben pertenecer a la pregunta indicada.');
            }
            if (!(new PreguntaModel())->update($id, ['pregunta' => $descripcion])) {
                throw new \RuntimeException('No se pudo actualizar la pregunta.');
            }
            foreach ($respuestas as $respuesta) {
                if (!$this->update($respuesta[0], ['respuesta' => $respuesta[1], 'valor' => $respuesta[2]])) {
                    throw new \RuntimeException('No se pudo actualizar una de las opciones.');
                }
            }
            return true;
        });
    }

    /**
     * Inserta las cuatro opciones de una pregunta dentro de una transacción.
     *
     * @param string $id Identificador del encabezado de pregunta existente.
     * @param list<array{0: string, 1: string, 2: int}> $respuestas Cuatro tuplas de ID, descripción y valor ya validadas.
     * @return bool True cuando las cuatro opciones se han insertado.
     * @throws \InvalidArgumentException Si no se reciben exactamente cuatro opciones.
     * @throws \Exception Si falla una inserción; revierte el grupo.
     */
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

    /**
     * Elimina un conjunto de opciones de forma atómica.
     *
     * @param list<string> $respuestas IDs de opciones que deben eliminarse; una lista vacía no escribe filas.
     * @return bool True si se completan todas las bajas solicitadas.
     * @throws \InvalidArgumentException Si falta alguna opción solicitada.
     * @throws \Exception Si falla una baja o una relación impide eliminarla; revierte el conjunto.
     */
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

    /**
     * Elimina una pregunta con sus opciones reales, incluidas preguntas antiguas incompletas.
     *
     * @param string $id Identificador del encabezado que se eliminará.
     * @param list<string> $ids IDs opcionales de confirmación; si se envían deben coincidir con las opciones reales.
     * @return void Confirma la baja completa del encabezado y sus opciones.
     * @throws \InvalidArgumentException Si la pregunta o las opciones de confirmación no coinciden.
     * @throws \Exception Si falla una baja; revierte la eliminación completa.
     */
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
