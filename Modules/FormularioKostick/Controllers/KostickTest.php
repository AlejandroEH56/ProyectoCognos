<?php

namespace Modules\FormularioKostick\Controllers;

use App\Controllers\BaseController;
use Modules\FormularioKostick\Models\DetPreguntaModel;

class KostickTest extends BaseController
{
    private DetPreguntaModel $dpreguntaM;

    public function __construct()
    {
        parent::__construct();
        $this->dpreguntaM = new DetPreguntaModel();
    }

    public function index()
    {
        $this->renderModule('formularioTest2', [
            'rendered' => $this, 'mensaje' => $_SESSION['mensaje'] ?? '', 'error' => $_SESSION['error'] ?? ''
        ] + $this->evaluationData('Kostick'));
    }

    public function viewEditTest()
    {
        $this->renderModule('updateFormTest2', [
            'rendered' => $this, 'mensaje' => $_SESSION['mensaje'] ?? '', 'error' => $_SESSION['error'] ?? ''
        ]);
    }

    public function formView()
    {
        return $this->renderModule('campoTest2', [
            'datosTest' => $this->dpreguntaM->getEvalTest2(),
            'anteriores' => $_SESSION['evaluacion_anterior']['Kostick']['respuestas'] ?? []
        ]);
    }

    public function editFormView()
    {
        return $this->renderModule('updateCampoTest2', ['datosTest' => $this->dpreguntaM->getEvalTest2()]);
    }

    private function readOptions(string $prefix): array
    {
        $options = [];
        for ($i = 1; $i <= 2; $i++) {
            $options[] = [
                'respuesta' => $this->postString($prefix . $i, 'descripción ' . $i, 65535),
                'valor' => $this->postInteger('valor' . $i, 'valor ' . $i)
            ];
        }
        return $options;
    }

    public function create()
    {
        try {
            $this->dpreguntaM->createPair($this->readOptions('descripcion'));
            $this->redirectMessage('/update-kostick', 'Pregunta creada con éxito.');
        } catch (\Throwable $error) {
            $this->redirectError('/update-kostick', $error);
        }
    }

    public function update()
    {
        try {
            $ids = [$this->postString('id_detpregunta1', 'opción 1'), $this->postString('id_detpregunta2', 'opción 2')];
            $this->dpreguntaM->updatePair($ids, $this->readOptions('respuesta'));
            $this->redirectMessage('/update-kostick', 'Respuestas actualizadas correctamente.');
        } catch (\Throwable $error) {
            $this->redirectError('/update-kostick', $error);
        }
    }

    public function delete()
    {
        try {
            $this->dpreguntaM->deletePair([
                $this->postString('id_detpregunta1', 'opción 1'), $this->postString('id_detpregunta2', 'opción 2')
            ]);
            $this->redirectMessage('/update-kostick', 'Pregunta eliminada correctamente.');
        } catch (\Throwable $error) {
            $this->redirectError('/update-kostick', $error);
        }
    }

    public function guardarEvaluacion(): void
    {
        $this->submitEvaluation('Kostick', '/test-kostick');
    }

    public function genPreguntaId(string $acronimo = 'dpreg'): string
    {
        return DetPreguntaModel::newId($acronimo);
    }

    public function getIdPreguntaKostick(): ?string
    {
        return $this->dpreguntaM->getIDPreguntaKostick()['ID'];
    }
}
