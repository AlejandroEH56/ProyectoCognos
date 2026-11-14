<?php

namespace Modules\Evaluaciones\Models;

use Modules\PerfilProfesional\Models\PerfilProfesionalModel;

/**
 * Valida respuestas y guarda aplicaciones con el perfil asociado a cada persona.
 */
class EvaluacionModel extends CuestionarioModel
{
    /**
     * Lista las personas activas junto con su perfil, incluso si aún no tienen asignación.
     *
     * @return list<array{id_persona: string, nombre: string|null, apellido: string|null, id_perfil: string|null, nombre_perfil: string|null, perfil_status: int|null}> Personas disponibles para la vista.
     * @throws \Exception Si falla la lectura SQL.
     */
    public function getPersonas(): array
    {
        return $this->queryPrepared(
            'SELECT p.id_persona, p.nombre, p.apellido, p.id_perfil, pp.nombre_perfil, pp.status AS perfil_status
             FROM persona p LEFT JOIN perfil_profesional pp ON pp.id_perfil = p.id_perfil
             WHERE p.status = 1 ORDER BY p.nombre, p.apellido, p.id_persona'
        );
    }

    /**
     * Calcula una huella del catálogo ordenado para detectar cambios antes del envío.
     *
     * @param string $nombre Nombre del test cuyo catálogo se serializa.
     * @return string Huella SHA-256 hexadecimal de las preguntas y opciones actuales.
     * @throws \JsonException Si el catálogo no puede codificarse como JSON.
     * @throws \Exception Si falla la lectura del catálogo.
     */
    public function catalogHash(string $nombre): string
    {
        return hash('sha256', json_encode($this->getOpciones($nombre), JSON_THROW_ON_ERROR));
    }

    /**
     * Valida respuestas completas y resuelve las opciones y valores que se guardarán.
     *
     * Kostick recibe una opción por clave pregNNN. Zavick recibe valores 1 a 4 por ID de opción,
     * sin repetir puntuaciones dentro de una pregunta.
     *
     * @param string $nombre Nombre del test que determina las reglas de validación.
     * @param array<string, mixed> $input Respuestas POST por clave de par o identificador de opción.
     * @return list<array{id_pregunta: string, pregunta: string|null, id_test: string, id_detpregunta: string, respuesta: string|null, valor: int}> Opciones seleccionadas con puntuación validada.
     * @throws \InvalidArgumentException Si el catálogo o las respuestas están incompletos o no corresponden.
     * @throws \Exception Si falla la consulta del catálogo.
     */
    private function selectedResponses(string $nombre, array $input): array
    {
        $catalog = $this->getOpciones($nombre);
        $options = array_values(array_filter($catalog, fn($row) => $row['id_detpregunta'] !== null));
        if (!$options) {
            throw new \InvalidArgumentException('El cuestionario todavía no tiene opciones para responder.');
        }
        $selected = [];
        if ($nombre === 'Kostick') {
            if (count($options) % 2 !== 0 || count($input) !== count($options) / 2) {
                throw new \InvalidArgumentException('Debes responder todos los pares vigentes de Kostick.');
            }
            for ($i = 0; $i < count($options); $i += 2) {
                $key = 'preg' . str_pad((string)($i / 2 + 1), 3, '0', STR_PAD_LEFT);
                $id = $input[$key] ?? null;
                $pair = [$options[$i]['id_detpregunta'] => $options[$i], $options[$i + 1]['id_detpregunta'] => $options[$i + 1]];
                if (!is_string($id) || !isset($pair[$id]) || $options[$i]['id_pregunta'] !== $options[$i + 1]['id_pregunta']) {
                    throw new \InvalidArgumentException('Una respuesta no pertenece a su par de Kostick.');
                }
                if ($options[$i]['valor'] === null || $options[$i + 1]['valor'] === null) {
                    throw new \InvalidArgumentException('Define los valores de ambas opciones antes de responder Kostick.');
                }
                $selected[] = $pair[$id];
            }
            return $selected;
        }

        $groups = array_fill_keys(array_column($catalog, 'id_pregunta'), []);
        if (count($input) !== count($options)) {
            throw new \InvalidArgumentException('Debes responder todas las opciones vigentes de Zavick.');
        }
        foreach ($options as $option) {
            $value = $input[$option['id_detpregunta']] ?? null;
            if (!is_string($value) || !in_array($value, ['1', '2', '3', '4'], true)) {
                throw new \InvalidArgumentException('Asigna un valor del 1 al 4 a cada opción de Zavick.');
            }
            $groups[$option['id_pregunta']][] = (int)$value;
            $option['valor'] = (int)$value;
            $selected[] = $option;
        }
        foreach ($groups as $values) {
            sort($values);
            if ($values !== [1, 2, 3, 4]) {
                throw new \InvalidArgumentException('Los valores del 1 al 4 deben aparecer una sola vez por pregunta.');
            }
        }
        return $selected;
    }

    /**
     * Guarda aplicación, relación con el test y respuestas en una transacción.
     *
     * Obtiene el perfil desde la persona y exige que esté activo para una aplicación nueva.
     * Un reintento idéntico devuelve la aplicación existente y conserva su perfil histórico.
     *
     * @param string $nombre Nombre del test que se está evaluando.
     * @param string $persona ID de una persona activa con perfil asignado.
     * @param array<string, mixed> $input Respuestas enviadas que deben validarse con el catálogo.
     * @param string $token Token hexadecimal de 32 caracteres validado por el controlador.
     * @param string $catalogHash Huella SHA-256 registrada al abrir el formulario.
     * @return string ID de la aplicación creada o recuperada por un reintento idéntico.
     * @throws \InvalidArgumentException Si cambió el catálogo, fallan las respuestas o la persona/perfil no es válida.
     * @throws \Exception Si falla la persistencia, serialización o generación de IDs; revierte la operación.
     */
    public function guardar(string $nombre, string $persona, array $input, string $token, string $catalogHash): string
    {
        return $this->transaction(function () use ($nombre, $persona, $input, $token, $catalogHash) {
            $test = $this->getTest($nombre, true);
            if (!hash_equals($catalogHash, $this->catalogHash($nombre))) {
                throw new \InvalidArgumentException('El cuestionario cambió. Revisa las opciones actuales antes de volver a enviarlo.');
            }
            $selected = $this->selectedResponses($nombre, $input);
            $applicationId = 'APL-' . $token;
            $existing = $this->queryOne(
                'SELECT a.id_persona, a.id_perfil, at.test_id_test FROM aplicacion a
                 JOIN aplicacion_test at ON at.aplicacion_id_aplicacion = a.id_aplicacion
                 WHERE a.id_aplicacion = ? FOR UPDATE',
                [$applicationId]
            );
            if ($existing) {
                $saved = $this->queryPrepared('SELECT id_detpregunta, valor FROM respuesta WHERE id_aplicacion = ?', [$applicationId]);
                $expectedValues = array_column($selected, 'valor', 'id_detpregunta');
                $savedValues = array_column($saved, 'valor', 'id_detpregunta');
                ksort($expectedValues);
                ksort($savedValues);
                if (
                    $existing['id_persona'] !== $persona
                    || $existing['test_id_test'] !== $test['id_test']
                    || array_map('intval', $expectedValues) !== array_map('intval', $savedValues)
                ) {
                    throw new \InvalidArgumentException('Este envío ya fue guardado con otros datos. Abre una nueva evaluación.');
                }
                return $applicationId;
            }
            $personaActual = $this->queryOne('SELECT id_perfil FROM persona WHERE id_persona = ? AND status = 1 FOR UPDATE', [$persona]);
            if ($personaActual === null) {
                throw new \InvalidArgumentException('Selecciona una persona activa.');
            }
            $perfil = $personaActual['id_perfil'];
            if ($perfil === null || $perfil === '') {
                throw new \InvalidArgumentException('Asigna un perfil profesional a la persona antes de realizar la evaluación.');
            }
            $perfilActual = (new PerfilProfesionalModel())->getPerfil($perfil, true);
            if ($perfilActual === null || (int)$perfilActual['status'] !== 1) {
                throw new \InvalidArgumentException('El perfil de la persona está inactivo. Actívalo o asigna otro perfil antes de evaluar.');
            }
            $now = new \DateTimeImmutable('now', new \DateTimeZone('America/Mexico_City'));
            $date = $now->format('Y-m-d');
            $time = $now->format('H:i:s');
            $this->executePrepared(
                'INSERT INTO aplicacion (id_aplicacion, fecha, hora, id_persona, id_perfil) VALUES (?, ?, ?, ?, ?)',
                [$applicationId, $date, $time, $persona, $perfil]
            );
            $this->executePrepared('INSERT INTO aplicacion_test (test_id_test, aplicacion_id_aplicacion) VALUES (?, ?)', [$test['id_test'], $applicationId]);
            foreach ($selected as $response) {
                $this->executePrepared(
                    'INSERT INTO respuesta (id_respuesta, fecha, hora, id_detpregunta, id_pregunta, id_aplicacion, valor) VALUES (?, ?, ?, ?, ?, ?, ?)',
                    [self::newId('RES'), $date, $time, $response['id_detpregunta'], $response['id_pregunta'], $applicationId, (int)$response['valor']]
                );
            }
            return $applicationId;
        });
    }
}
