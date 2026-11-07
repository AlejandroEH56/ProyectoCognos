<?php

namespace App\Models;

class EvaluacionModel extends CuestionarioModel
{
    public function getPersonas(): array
    {
        return $this->queryPrepared('SELECT id_persona, nombre, apellido FROM persona WHERE status = 1 ORDER BY nombre, apellido, id_persona');
    }

    public function getPerfiles(): array
    {
        return $this->queryPrepared('SELECT id_perfil, nombre_perfil FROM perfil_profesional WHERE status = 1 ORDER BY nombre_perfil, id_perfil');
    }

    public function catalogHash(string $nombre): string
    {
        return hash('sha256', json_encode($this->getOpciones($nombre), JSON_THROW_ON_ERROR));
    }

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

    public function guardar(string $nombre, string $persona, string $perfil, array $input, string $token, string $catalogHash): string
    {
        return $this->transaction(function () use ($nombre, $persona, $perfil, $input, $token, $catalogHash) {
            $test = $this->getTest($nombre, true);
            if (!hash_equals($catalogHash, $this->catalogHash($nombre))) {
                throw new \InvalidArgumentException('El cuestionario cambió. Revisa las opciones actuales antes de volver a enviarlo.');
            }
            $selected = $this->selectedResponses($nombre, $input);
            $applicationId = 'APL-' . $token;
            $existing = $this->queryPrepared(
                'SELECT a.id_persona, a.id_perfil, at.test_id_test FROM aplicacion a
                 JOIN aplicacion_test at ON at.aplicacion_id_aplicacion = a.id_aplicacion
                 WHERE a.id_aplicacion = ? FOR UPDATE', [$applicationId]
            );
            if ($existing) {
                $saved = $this->queryPrepared('SELECT id_detpregunta, valor FROM respuesta WHERE id_aplicacion = ?', [$applicationId]);
                $expectedValues = array_column($selected, 'valor', 'id_detpregunta');
                $savedValues = array_column($saved, 'valor', 'id_detpregunta');
                ksort($expectedValues);
                ksort($savedValues);
                if ($existing[0]['id_persona'] !== $persona || $existing[0]['id_perfil'] !== $perfil
                    || $existing[0]['test_id_test'] !== $test['id_test']
                    || array_map('intval', $expectedValues) !== array_map('intval', $savedValues)) {
                    throw new \InvalidArgumentException('Este envío ya fue guardado con otros datos. Abre una nueva evaluación.');
                }
                return $applicationId;
            }
            if (!$this->queryPrepared('SELECT id_persona FROM persona WHERE id_persona = ? AND status = 1 FOR UPDATE', [$persona])) {
                throw new \InvalidArgumentException('Selecciona una persona activa.');
            }
            if (!$this->queryPrepared('SELECT id_perfil FROM perfil_profesional WHERE id_perfil = ? AND status = 1 FOR UPDATE', [$perfil])) {
                throw new \InvalidArgumentException('Selecciona un perfil activo.');
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
