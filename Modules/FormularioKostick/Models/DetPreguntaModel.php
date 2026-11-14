<?php

namespace Modules\FormularioKostick\Models;

use Modules\Evaluaciones\Models\CuestionarioModel;

/**
 * Opciones y operaciones atómicas sobre pares del cuestionario Kostick.
 */
class DetPreguntaModel extends CuestionarioModel
{
    /**
     * @var string Tabla que almacena las opciones de las preguntas.
     */
    protected string $table = 'det_pregunta';
    /**
     * @var string Identificador único de una opción.
     */
    protected string $primaryKey = 'id_detpregunta';
    /**
     * @var list<string> Columnas permitidas para crear o editar opciones.
     */
    protected array $fillable = ['id_detpregunta', 'respuesta', 'id_pregunta', 'valor'];

    /**
     * Consulta las opciones de Kostick en el orden que define sus pares.
     *
     * @return list<array{id_pregunta: string, pregunta: string|null, id_test: string, id_detpregunta: string, respuesta: string|null, valor: int|null}> Opciones existentes, sin filas contenedoras vacías.
     * @throws \Exception Si falla la lectura.
     */
    public function getEvalTest2(): array
    {
        return array_values(array_filter($this->getOpciones('Kostick'), fn($row) => $row['id_detpregunta'] !== null));
    }

    /**
     * Inserta una opción con el identificador y la pregunta proporcionados.
     *
     * @param array{id_detpregunta: string, respuesta: string, id_pregunta: string, valor: int} $dataPregunta Datos completos de la opción.
     * @return bool True si se insertó una fila; false si no se insertaron filas.
     * @throws \Exception Si falla la persistencia.
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
     * Actualiza la descripción y el valor de una opción por su identificador.
     *
     * @param string $id ID de la opción que se desea actualizar.
     * @param array{respuesta: string, valor: int} $dataPregunta Descripción y puntuación nuevas.
     * @return int Filas modificadas; cero si no existe el ID o los valores ya eran iguales.
     * @throws \Exception Si falla la sentencia SQL.
     */
    public function updateDetPregunta(string $id, array $dataPregunta): int
    {
        $data = [$dataPregunta['respuesta'], $dataPregunta['valor'], $id];
        $result = $this->executePrepared("UPDATE det_pregunta SET respuesta = ?, valor = ? WHERE id_detpregunta = ?", $data);
        return $result;
    }

    /**
     * Elimina físicamente una opción por su identificador.
     *
     * @param string $id ID de la opción que se eliminará.
     * @return bool True si eliminó una fila; false si no encontró el ID.
     * @throws \Exception Si falla la escritura, incluidas relaciones con respuestas guardadas.
     */
    public function deleteDetPregunta(string $id): bool
    {
        return $this->delete($id);
    }


    /**
     * Cuenta todas las opciones de la tabla, de cualquier test.
     *
     * @return array{total: int} Total de filas de det_pregunta.
     * @throws \Exception Si falla el conteo.
     */
    public function getRespuestaCount(): array
    {
        return ['total' => $this->count()];
    }

    /**
     * Obtiene la primera pregunta contenedora del test Kostick.
     *
     * @return array{ID: string|null} ID de la pregunta, o null si todavía no existe.
     * @throws \Exception Si falla la consulta.
     */
    public function getIDPreguntaKostick(): array
    {
        $row = $this->queryOne(
            'SELECT p.id_pregunta AS ID FROM pregunta p JOIN test t ON t.id_test = p.id_test
             WHERE t.id_test = (SELECT id_test FROM test WHERE nombre = ? ORDER BY id_test LIMIT 1)
             ORDER BY p.id_pregunta LIMIT 1', ['Kostick']
        );
        return $row ?? ['ID' => null];
    }

    /**
     * Crea dos opciones y, si hace falta, la pregunta contenedora de Kostick.
     *
     * @param list<array{respuesta: string, valor: int}> $opciones Exactamente dos opciones ya validadas por el controlador.
     * @return void Confirma el par completo dentro de una transacción.
     * @throws \InvalidArgumentException Si no se reciben dos opciones o el test no está registrado.
     * @throws \Exception Si falla la creación o la generación de IDs; revierte el par.
     */
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

    /**
     * Bloquea el test y comprueba que los IDs formen un par vigente.
     *
     * El llamador debe mantener una transacción activa durante la operación del par.
     *
     * @param list<string> $ids Dos identificadores distintos de opciones de la misma pareja.
     * @return void Finaliza cuando el par es válido.
     * @throws \InvalidArgumentException Si los IDs no corresponden a un par vigente.
     * @throws \Exception Si falla la consulta o el bloqueo.
     */
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

    /**
     * Actualiza las dos opciones de un par como una sola operación atómica.
     *
     * @param list<string> $ids Dos IDs que corresponden al mismo par vigente.
     * @param list<array{respuesta: string, valor: int}> $opciones Dos opciones validadas, en el orden de ids.
     * @return void Confirma ambas escrituras, incluidos guardados sin cambios.
     * @throws \InvalidArgumentException Si los IDs no forman un par válido.
     * @throws \Exception Si falla una escritura; revierte el conjunto.
     */
    public function updatePair(array $ids, array $opciones): void
    {
        $this->transaction(function () use ($ids, $opciones) {
            $this->lockPair($ids);
            foreach ($ids as $index => $id) {
                $this->updateDetPregunta($id, $opciones[$index]);
            }
        });
    }

    /**
     * Elimina las dos opciones de una pareja dentro de una transacción.
     *
     * @param list<string> $ids Dos IDs distintos del mismo par vigente.
     * @return void Confirma la eliminación completa del par.
     * @throws \InvalidArgumentException Si los IDs no corresponden a un par.
     * @throws \Exception Si falla una baja o una clave foránea impide eliminarla; revierte el conjunto.
     */
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
