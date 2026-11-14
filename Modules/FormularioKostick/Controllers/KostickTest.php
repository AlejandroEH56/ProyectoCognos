<?php

namespace Modules\FormularioKostick\Controllers;

use Modules\Evaluaciones\Controllers\EvaluacionController;
use Modules\FormularioKostick\Models\DetPreguntaModel;

/**
 * Presentación, administración de pares y envío de evaluaciones Kostick.
 */
class KostickTest extends EvaluacionController
{
    /**
     * @var DetPreguntaModel Modelo que consulta y administra los pares de Kostick.
     */
    private DetPreguntaModel $dpreguntaM;

    /**
     * Prepara el modelo de opciones una vez disponible la sesión.
     *
     * @return void Asigna dpreguntaM para las acciones de Kostick.
     * @throws \Exception Si no se puede obtener la conexión del modelo.
     */
    protected function initialize(): void
    {
        $this->dpreguntaM = new DetPreguntaModel();
    }

    /**
     * Muestra el formulario Kostick con personas, perfil automático y token de sesión.
     *
     * Entrada de sesión: mensajes y respuestas anteriores de Kostick.
     *
     * @return void Escribe el formulario HTML.
     * @throws \Exception Si falla la preparación de datos o la resolución de la vista.
     */
    public function index()
    {
        $this->renderModule('formularioTest2', [
            'rendered' => $this, 'mensaje' => $_SESSION['mensaje'] ?? '', 'error' => $_SESSION['error'] ?? ''
        ] + $this->evaluationData('Kostick'));
    }

    /**
     * Muestra la pantalla de administración y sus mensajes de sesión.
     *
     * @return void Escribe la página HTML de edición de Kostick.
     * @throws \Exception Si la vista no puede resolverse.
     */
    public function viewEditTest()
    {
        $this->renderModule('updateFormTest2', [
            'rendered' => $this, 'mensaje' => $_SESSION['mensaje'] ?? '', 'error' => $_SESSION['error'] ?? ''
        ]);
    }

    /**
     * Incluye los pares vigentes y recupera las selecciones previas de la sesión.
     *
     * @return null Escribe el fragmento HTML; no devuelve su contenido.
     * @throws \Exception Si falla la consulta o la resolución de la vista.
     */
    public function formView()
    {
        return $this->renderModule('campoTest2', [
            'datosTest' => $this->dpreguntaM->getEvalTest2(),
            'anteriores' => $_SESSION['evaluacion_anterior']['Kostick']['respuestas'] ?? []
        ]);
    }

    /**
     * Incluye los campos de edición con las opciones y valores actuales.
     *
     * @return null Escribe el fragmento HTML de edición.
     * @throws \Exception Si falla la consulta o la resolución de la vista.
     */
    public function editFormView()
    {
        return $this->renderModule('updateCampoTest2', ['datosTest' => $this->dpreguntaM->getEvalTest2()]);
    }

    /**
     * Lee las dos descripciones y puntuaciones administrativas recibidas por POST.
     *
     * @param string $prefix Prefijo de las descripciones: descripcion para alta o respuesta para edición.
     * @return list<array{respuesta: string, valor: int}> Dos opciones recortadas con valores enteros.
     * @throws \InvalidArgumentException Si falta una descripción o un valor no es válido.
     */
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

    /**
     * Crea un par completo a partir de los campos POST descripcion1/2 y valor1/2.
     *
     * @return never Redirige a /update-kostick con la confirmación o el error.
     */
    public function create()
    {
        try {
            $this->dpreguntaM->createPair($this->readOptions('descripcion'));
            $this->redirectMessage('/update-kostick', 'Pregunta creada con éxito.');
        } catch (\Throwable $error) {
            $this->redirectError('/update-kostick', $error);
        }
    }

    /**
     * Edita un par validando los identificadores de ambas opciones.
     *
     * Entrada POST: id_detpregunta1/2, respuesta1/2 y valor1/2.
     *
     * @return never Redirige a /update-kostick con el resultado.
     */
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

    /**
     * Elimina un par completo indicado en el formulario de confirmación.
     *
     * Entrada POST: id_detpregunta1 e id_detpregunta2.
     *
     * @return never Redirige a /update-kostick con el resultado.
     */
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

    /**
     * Procesa una evaluación Kostick usando el flujo compartido del módulo.
     *
     * Entrada POST: id_persona, evaluacion_token y respuestas[pregNNN].
     *
     * @return never Guarda o recupera la captura fallida y redirige a /test-kostick.
     */
    public function guardarEvaluacion(): void
    {
        $this->submitEvaluation('Kostick', '/test-kostick');
    }

    /**
     * Genera un identificador aleatorio para opciones de pregunta.
     *
     * @param string $acronimo Prefijo del nuevo identificador.
     * @return string Identificador con prefijo y sufijo hexadecimal.
     * @throws \Random\RandomException Si falla la generación aleatoria.
     */
    public function genPreguntaId(string $acronimo = 'dpreg'): string
    {
        return DetPreguntaModel::newId($acronimo);
    }

    /**
     * Consulta la primera pregunta contenedora del test Kostick.
     *
     * @return string|null ID de la pregunta; null cuando todavía no hay ninguna.
     * @throws \Exception Si falla la consulta.
     */
    public function getIdPreguntaKostick(): ?string
    {
        return $this->dpreguntaM->getIDPreguntaKostick()['ID'];
    }
}
