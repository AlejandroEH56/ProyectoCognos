<?php

namespace Modules\FormularioZavick\Controllers;

use App\Controllers\BaseController;
use Modules\FormularioZavick\Models\DetPreguntaModel;
use Modules\FormularioZavick\Models\PreguntaModel;

class ZavickTest extends BaseController
{
    private DetPreguntaModel $dPreguntaM;
    private PreguntaModel $preguntaM;

    public function __construct()
    {
        parent::__construct();
        $this->dPreguntaM = new DetPreguntaModel();
        $this->preguntaM = new PreguntaModel();
    }

    public function index()
    {
        $this->renderModule('formularioTest3', [
            'form' => $this, 'mensaje' => $_SESSION['mensaje'] ?? '', 'error' => $_SESSION['error'] ?? ''
        ] + $this->evaluationData('Zavick'));
    }

    public function formPreguntas()
    {
        return $this->renderModule('campoTest3', [
            'datosTest' => $this->sanitizedData(),
            'anteriores' => $_SESSION['evaluacion_anterior']['Zavick']['respuestas'] ?? []
        ]);
    }

    public function updateform()
    {
        $this->renderModule('updateZavick', [
            'view' => $this, 'mensaje' => $_SESSION['mensaje'] ?? '', 'error' => $_SESSION['error'] ?? ''
        ]);
    }

    public function updateField()
    {
        return $this->renderModule('updateCampoZavick', ['datosTest' => $this->formatData()]);
    }

    public function sanitizedData(): array
    {
        $questions = $this->formatData();
        foreach ($questions as $index => &$question) {
            foreach ($question['opciones'] as &$option) {
                $option['grupo'] = 'grupo' . ($index + 1);
                $option['nombre'] = 'respuestas[' . $option['idRes'] . ']';
            }
            unset($option);
        }
        unset($question);
        return $questions;
    }

    public function formatData(): array
    {
        return $this->dPreguntaM->getEvalTest3();
    }

    private function readRespuestas(bool $updating): array
    {
        $responses = [];
        for ($i = 1; $i <= 4; $i++) {
            $responses[] = [
                $updating ? ($_POST['id_respuesta' . $i] ?? null) : $this->genIDRespuesta(),
                $_POST['des_respuesta' . $i] ?? null, $_POST['des_valor' . $i] ?? null
            ];
        }
        $validation = $this->validateRespuestas($responses);
        if ($validation['error']) {
            throw new \InvalidArgumentException($validation['mensaje']);
        }
        return array_map(fn($row) => [trim($row[0]), trim($row[1]), (int)$row[2]], $responses);
    }

    public function create(): void
    {
        try {
            $description = $this->postString('des_pregunta', 'pregunta');
            $responses = $this->readRespuestas(false);
            $this->dPreguntaM->createQuestion([
                'id_pregunta' => $this->genIDPregunta(), 'pregunta' => $description, 'tipo_respuesta' => 1
            ], $responses);
            $this->redirectMessage('/update-zavick', 'Pregunta creada correctamente.');
        } catch (\Throwable $error) {
            $this->redirectError('/update-zavick', $error);
        }
    }

    public function update(): void
    {
        try {
            $this->dPreguntaM->updatePregunta(
                $this->postString('id_pregunta', 'pregunta'), $this->postString('des_pregunta', 'descripción de pregunta'),
                $this->readRespuestas(true)
            );
            $this->redirectMessage('/update-zavick', 'Pregunta actualizada correctamente.');
        } catch (\Throwable $error) {
            $this->redirectError('/update-zavick', $error);
        }
    }

    public function validateRespuestas(array $respuestas): array
    {
        if (count($respuestas) !== 4) {
            return ['error' => true, 'mensaje' => 'Se requieren cuatro opciones.'];
        }
        $ids = [];
        foreach ($respuestas as $index => $respuesta) {
            if (!is_array($respuesta) || !is_string($respuesta[0] ?? null) || trim($respuesta[0]) === ''
                || mb_strlen($respuesta[0], 'UTF-8') > 255 || !is_string($respuesta[1] ?? null)
                || trim($respuesta[1]) === '' || strlen($respuesta[1]) > 65535
                || (!is_string($respuesta[2] ?? null) && !is_int($respuesta[2] ?? null))
                || filter_var($respuesta[2], FILTER_VALIDATE_INT, ['options' => ['min_range' => -2147483648, 'max_range' => 2147483647]]) === false) {
                return ['error' => true, 'mensaje' => 'Revisa la descripción, el identificador y el valor entero de la opción ' . ($index + 1) . '.'];
            }
            $ids[] = trim($respuesta[0]);
        }
        if (count(array_unique($ids)) !== 4) {
            return ['error' => true, 'mensaje' => 'Los identificadores de las opciones deben ser diferentes.'];
        }
        return ['error' => false, 'mensaje' => 'Sin errores.'];
    }

    public function delete(): void
    {
        try {
            $ids = [];
            for ($i = 1; $i <= 4; $i++) {
                $key = 'id_detpregunta' . $i;
                if (isset($_POST[$key]) && $_POST[$key] !== '') {
                    $ids[] = $this->postString($key, 'opción ' . $i);
                }
            }
            $this->dPreguntaM->deleteQuestion($this->postString('id_pregunta', 'pregunta'), $ids);
            $this->redirectMessage('/update-zavick', 'Pregunta eliminada correctamente.');
        } catch (\Throwable $error) {
            $this->redirectError('/update-zavick', $error);
        }
    }

    public function guardarEvaluacion(): void
    {
        $this->submitEvaluation('Zavick', '/test-zavic');
    }

    public function genIDPregunta(string $acronimo = 'preg'): string
    {
        return DetPreguntaModel::newId($acronimo);
    }

    public function genIDRespuesta(string $acronimo = 'dpreg', int $cantidad = 1): string|array
    {
        if ($cantidad < 1) {
            throw new \InvalidArgumentException('La cantidad de identificadores debe ser positiva.');
        }
        $ids = [];
        for ($i = 0; $i < $cantidad; $i++) {
            $ids[] = DetPreguntaModel::newId($acronimo);
        }
        return $cantidad === 1 ? $ids[0] : $ids;
    }

    public function getIDTest(): array
    {
        return $this->preguntaM->getIDTest();
    }
}
