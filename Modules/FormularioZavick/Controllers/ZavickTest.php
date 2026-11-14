<?php

namespace Modules\FormularioZavick\Controllers;

use Modules\Evaluaciones\Controllers\EvaluacionController;
use Modules\FormularioZavick\Models\DetPreguntaModel;
use Modules\FormularioZavick\Models\PreguntaModel;

/**
 * Acciones de presentación, administración y captura del cuestionario Zavick.
 */
class ZavickTest extends EvaluacionController
{
    /**
     * @var DetPreguntaModel Modelo de opciones y operaciones compuestas de preguntas.
     */
    private DetPreguntaModel $dPreguntaM;
    /**
     * @var PreguntaModel Modelo de los encabezados de pregunta de Zavick.
     */
    private PreguntaModel $preguntaM;

    /**
     * Prepara los modelos de preguntas y opciones después de iniciar la sesión.
     *
     * @return void Asigna dPreguntaM y preguntaM a esta instancia.
     * @throws \Exception Si la conexión de los modelos no está disponible.
     */
    protected function initialize(): void
    {
        $this->dPreguntaM = new DetPreguntaModel();
        $this->preguntaM = new PreguntaModel();
    }

    /**
     * Muestra el formulario Zavick con contexto de persona, perfil y token.
     *
     * Entrada de sesión: mensajes y selecciones anteriores de Zavick.
     *
     * @return void Escribe la página HTML de evaluación.
     * @throws \Exception Si falla la lectura o la resolución de la vista.
     */
    public function index()
    {
        $this->renderModule('formularioTest3', [
            'form' => $this, 'mensaje' => $_SESSION['mensaje'] ?? '', 'error' => $_SESSION['error'] ?? ''
        ] + $this->evaluationData('Zavick'));
    }

    /**
     * Incluye las preguntas con grupos de controles y respuestas anteriores.
     *
     * @return null Escribe el fragmento HTML del cuestionario.
     * @throws \Exception Si falla la consulta o la resolución de la vista.
     */
    public function formPreguntas()
    {
        return $this->renderModule('campoTest3', [
            'datosTest' => $this->sanitizedData(),
            'anteriores' => $_SESSION['evaluacion_anterior']['Zavick']['respuestas'] ?? []
        ]);
    }

    /**
     * Muestra la pantalla de administración con sus mensajes de sesión.
     *
     * @return void Escribe la página HTML de edición.
     * @throws \Exception Si no puede resolverse la vista.
     */
    public function updateform()
    {
        $this->renderModule('updateZavick', [
            'view' => $this, 'mensaje' => $_SESSION['mensaje'] ?? '', 'error' => $_SESSION['error'] ?? ''
        ]);
    }

    /**
     * Incluye los campos editables de cada pregunta y sus opciones.
     *
     * @return null Escribe el fragmento HTML de administración.
     * @throws \Exception Si falla la consulta o la resolución de la vista.
     */
    public function updateField()
    {
        return $this->renderModule('updateCampoZavick', ['datosTest' => $this->formatData()]);
    }

    /**
     * Añade grupo y nombre de campo HTTP a cada opción sin modificar su texto.
     *
     * @return list<array{id_pregunta: string, pregunta: string|null, opciones: list<array{idRes: string, campo: string|null, valor: int|null, grupo: string, nombre: string}>}> Preguntas listas para renderizar controles independientes.
     * @throws \Exception Si falla la lectura del cuestionario.
     */
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

    /**
     * Obtiene las preguntas y sus opciones como arreglos estructurados.
     *
     * @return list<array{id_pregunta: string, pregunta: string|null, opciones: list<array{idRes: string, campo: string|null, valor: int|null}>}> Datos ordenados del cuestionario Zavick.
     * @throws \Exception Si falla la consulta.
     */
    public function formatData(): array
    {
        return $this->dPreguntaM->getEvalTest3();
    }

    /**
     * Lee y valida las cuatro opciones administrativas recibidas por POST.
     *
     * Entrada POST: des_respuesta1..4, des_valor1..4 e id_respuesta1..4 al editar.
     *
     * @param bool $updating Si es true conserva los IDs enviados; si es false genera IDs nuevos.
     * @return list<array{0: string, 1: string, 2: int}> Tuplas de ID, descripción recortada y valor entero.
     * @throws \InvalidArgumentException Si las cuatro opciones o sus identificadores no son válidos.
     * @throws \Exception Si falla la generación de identificadores.
     */
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

    /**
     * Crea una pregunta con sus cuatro opciones dentro de una operación atómica.
     *
     * Entrada POST: des_pregunta, des_respuesta1..4 y des_valor1..4.
     *
     * @return never Redirige a /update-zavick con la confirmación o el error.
     */
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

    /**
     * Actualiza una pregunta y sus cuatro opciones conservando su pertenencia.
     *
     * Entrada POST: id_pregunta, des_pregunta, id_respuesta1..4, des_respuesta1..4 y des_valor1..4.
     *
     * @return never Redirige a /update-zavick con el resultado.
     */
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

    /**
     * Comprueba cantidad, identificadores distintos, descripciones y valores de las opciones.
     *
     * @param array<array-key, mixed> $respuestas Captura a validar; se esperan cuatro tuplas [ID, descripción, valor].
     * @return array{error: bool, mensaje: string} Resultado de la validación; no lanza errores por campos inválidos.
     */
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

    /**
     * Elimina una pregunta y las opciones que realmente le pertenecen.
     *
     * Entrada POST: id_pregunta y los campos opcionales id_detpregunta1..4 de la confirmación.
     *
     * @return never Redirige a /update-zavick con la confirmación o el error.
     */
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

    /**
     * Envía la evaluación Zavick al flujo compartido de guardado.
     *
     * Entrada POST: id_persona, evaluacion_token y respuestas[ID_OPCION] con puntuaciones 1 a 4.
     *
     * @return never Guarda o recupera la captura fallida y redirige a /test-zavic.
     */
    public function guardarEvaluacion(): void
    {
        $this->submitEvaluation('Zavick', '/test-zavic');
    }

    /**
     * Genera un identificador aleatorio para un encabezado de pregunta.
     *
     * @param string $acronimo Prefijo del identificador nuevo.
     * @return string ID de pregunta con prefijo y sufijo hexadecimal.
     * @throws \Random\RandomException Si no se pueden obtener bytes aleatorios.
     */
    public function genIDPregunta(string $acronimo = 'preg'): string
    {
        return DetPreguntaModel::newId($acronimo);
    }

    /**
     * Genera uno o varios identificadores aleatorios de opciones.
     *
     * @param string $acronimo Prefijo común de los identificadores nuevos.
     * @param int $cantidad Número positivo de IDs que se generarán.
     * @return string|list<string> Un string cuando cantidad es 1; una lista para cantidades mayores.
     * @throws \InvalidArgumentException Si cantidad es menor que uno.
     * @throws \Random\RandomException Si falla la generación aleatoria.
     */
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

    /**
     * Obtiene el identificador del test Zavick registrado.
     *
     * @return array{ID: string} Identificador del test bajo la clave ID.
     * @throws \InvalidArgumentException Si no está registrado el test.
     * @throws \Exception Si falla la consulta.
     */
    public function getIDTest(): array
    {
        return $this->preguntaM->getIDTest();
    }
}
