<?php

namespace Modules\FormularioZavick\Controllers;

use App\Controllers\BaseController;
use Modules\FormularioZavick\Models\DetPreguntaModel;
use Modules\FormularioZavick\Models\PreguntaModel;

class ZavickTest extends BaseController
{
    private $dPreguntaM;
    private $preguntaM;
    public function __construct()
    {
        $this->dPreguntaM = new DetPreguntaModel();
        $this->preguntaM = new PreguntaModel();
    }

    /**
     * Devuelve la vista de la evaluacion del test 3 (Zavic)
     * @return void
     */
    public function index()
    {
        $form = new ZavickTest();
        $mensaje = $_SESSION['mensaje'] ?? '';
        $error = $_SESSION['error'] ?? '';
        $this->renderModule('formularioTest3', [
            'form' => $form,
            'mensaje' => $mensaje,
            'error' => $error
        ]);
    }

    /**
     * Muestra el formulario de preguntas ya renderizado
     */
    public function formPreguntas()
    {
        $datosTest = $this->sanitizedData();

        $mensaje = $_SESSION['mensaje'] ?? '';
        $error = $_SESSION['error'] ?? '';
        return $this->renderModule('campoTest3', [
            'mensaje' => $mensaje,
            'error' => $error,
            'datosTest' => $datosTest,
        ]);
    }

    /**
     * Muestra el formulario de preguntas ya renderizado (para editar)
     */
    public function updateform()
    {
        $view = new ZavickTest();
        $mensaje = $_SESSION['mensaje'] ?? '';
        $error = $_SESSION['error'] ?? '';
        $this->renderModule('updateZavick', [
            'view' => $view,
            'mensaje' => $mensaje,
            'error' => $error,
        ]);
    }

    /**
     * Muestra el formulario de preguntas ya renderizado
     */
    public function updateField()
    {
        $datosTest = $this->formatData();

        $mensaje = $_SESSION['mensaje'] ?? '';
        $error = $_SESSION['error'] ?? '';
        return $this->renderModule('updateCampoZavick', [
            'mensaje' => $mensaje,
            'error' => $error,
            'datosTest' => $datosTest,
        ]);
    }

    /**
     * Sanitiza los datos del Test
     * @return array{descripcion: string, preguntas: array}
     */
    public function sanitizedData(): array
    {
        $formPreguntas = $this->dPreguntaM->getEvalTest3();

        $dataForm = [];
        foreach ($formPreguntas as $pg => $pregunta) {
            $dataItem = [
                'pregunta' => $pregunta['pregunta'],
                'opciones' => []
            ];

            $pieces = explode("/", $pregunta['opciones']);
            foreach ($pieces as $op) {
                $dataItem['opciones'][] = [
                    'campo' => trim($op),
                    'grupo' => 'grupo' . ($pg + 1),
                    'nombre' => strtolower(preg_replace('/[^a-z0-9_]/', '', str_replace(' ', '_', trim($op)))) // genera nombre dinámico
                ];
            }

            $dataForm[] = $dataItem;
        }
        return $dataForm;
    }

    /**
     * Formatea los datos del test para los campos
     * @return array{descripcion: string, preguntas: array}
     */
    public function formatData(): array
    {
        $formPreguntas = $this->dPreguntaM->getEvalTest3();

        $dataForm = [];
        foreach ($formPreguntas as $pregunta) {
            $dataItem = [
                'id_pregunta' => $pregunta['id_pregunta'],
                'pregunta' => $pregunta['pregunta'],
                'opciones' => []
            ];

            $pieces = explode("/", $pregunta['opciones']);
            $pieces2 = explode("/", $pregunta['valores']);
            $pieces3 = explode("/", $pregunta['identificadores']);
            $count = max(count($pieces), count($pieces2));
            for ($i = 0; $i < $count; $i++) {
                $campo = isset($pieces[$i]) ? trim($pieces[$i]) : '';
                $valor = isset($pieces2[$i]) ? trim($pieces2[$i]) : '';
                $idRes = isset($pieces3[$i]) ? trim($pieces3[$i]) : '';
                $dataItem['opciones'][] = [
                    'idRes' => $idRes,
                    'campo' => $campo,
                    'valor' => $valor,
                ];
            }

            $dataForm[] = $dataItem;
        }
        return $dataForm;
    }

    /**
     * Agrega una nueva pregunta y sus respuestas
     * @return void
     */
    public function create(): void
    {
        if (empty($_POST['des_pregunta'])) {
            $_SESSION['error'] = 'No hay datos para crear la pregunta.';
            header('Location: /update-zavick');
            exit;
        }

        $test = $this->getIDtest();

        $dataPregunta = [
            'id_pregunta' => $this->genIDPregunta(),
            'pregunta' => $_POST['des_pregunta'],
            'tipo_respuesta' => 1,
            'id_test' => $test['ID'],
        ];

        $respuestap = $this->preguntaM->createPregunta($dataPregunta);
        if (!$respuestap) {
            $_SESSION['error'] = 'Error al crear la pregunta.';
            header('Location: /update-zavick');
            exit;
        }

        $idsGenerados = $this->genIDRespuesta('dpreg', 4);

        $respuestas = [
            [$idsGenerados[0], $_POST['des_respuesta1'] ?? '', $_POST['des_valor1'] ?? '',],
            [$idsGenerados[1], $_POST['des_respuesta2'] ?? '', $_POST['des_valor2'] ?? '',],
            [$idsGenerados[2], $_POST['des_respuesta3'] ?? '', $_POST['des_valor3'] ?? '',],
            [$idsGenerados[3], $_POST['des_respuesta4'] ?? '', $_POST['des_valor4'] ?? '',],
        ];

        try {
            $respuestadp = $this->dPreguntaM->createDetPreguntas($dataPregunta['id_pregunta'], $respuestas);

            if ($respuestadp == true) {
                $_SESSION['mensaje'] = 'Pregunta creada correctamente.';
                header('Location: /update-zavick');
                exit;
            } else {
                // Si retorna false (aunque en tu caso no debería, por consistencia)
                $_SESSION['error'] = 'Error desconocido al actualizar la pregunta.';
                header('Location: /update-zavick');
                exit;
            }
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Error al actualizar la pregunta: ' . $e->getMessage();
            header('Location: /update-zavick');
            exit;
        }
    }

    /**
     * Genera el ID de la pregunta (detPregunta)
     * @param string $acronimo
     * @return string
     */
    public function genIDPregunta(string $acronimo = 'preg'): string
    {
        $numeroConsecutivo = $this->preguntaM->getPreguntaCount();
        // Formatear número con ceros a la izquierda (mínimo 3 dígitos) y formatear fecha y hora
        $numeroFormateado = str_pad($numeroConsecutivo['total'] + 1, 3, '0', STR_PAD_LEFT);
        $fechaHora = date('dmYHis');

        return "{$acronimo}-{$numeroFormateado}-{$fechaHora}";
    }

    /**
     * Genera uno o más IDs únicos para respuestas.
     * @param string $acronimo Prefijo del ID (default 'dpreg').
     * @param int $cantidad Número de IDs a generar (default 1).
     * @return string|array Retorna un string si $cantidad=1, o un array de strings si >1.
     */
    public function genIDRespuesta(string $acronimo = 'dpreg', int $cantidad = 1): string|array
    {
        // Obtener el conteo actual de registros una sola vez
        $numeroConsecutivo = $this->dPreguntaM->getRespuestaCount();
        $baseNumero = $numeroConsecutivo['total'] + 1;
        $fechaHora = date('dmYHis');

        // Si solo se pide 1 ID, retornar string
        if ($cantidad === 1) {
            $numeroFormateado = str_pad($baseNumero, 3, '0', STR_PAD_LEFT);
            return "{$acronimo}-{$numeroFormateado}-{$fechaHora}";
        }

        // Si se piden múltiples IDs, retornar array
        $ids = [];
        for ($i = 0; $i < $cantidad; $i++) {
            $numeroFormateado = str_pad($baseNumero + $i, 3, '0', STR_PAD_LEFT);
            $ids[] = "{$acronimo}-{$numeroFormateado}-{$fechaHora}";
        }
        return $ids;
    }

    /**
     * Devuelve la cadena del ID zavick
     * @return array
     */
    public function getIDTest(): array
    {
        $val = $this->preguntaM->getIDTest();
        return $val;
    }

    /**
     * Actualiza los datos de una pregunta
     * @return void
     */
    public function update(): void
    {
        $id = $_POST['id_pregunta'] ?? '';
        $descripcion = $_POST['des_pregunta'] ?? '';
        if (empty($id) || empty($descripcion)) {
            $_SESSION['error'] = 'ID y/o descripción de pregunta es requerido.';
            header('Location: /update-zavick');
            exit;
        }
        $respuestas = [
            [$_POST['id_respuesta1'] ?? '', $_POST['des_respuesta1'] ?? '', $_POST['des_valor1'] ?? '',],
            [$_POST['id_respuesta2'] ?? '', $_POST['des_respuesta2'] ?? '', $_POST['des_valor2'] ?? '',],
            [$_POST['id_respuesta3'] ?? '', $_POST['des_respuesta3'] ?? '', $_POST['des_valor3'] ?? '',],
            [$_POST['id_respuesta4'] ?? '', $_POST['des_respuesta4'] ?? '', $_POST['des_valor4'] ?? '',],
        ];

        $valid = $this->validateRespuestas($respuestas);
        if ($valid['error']) {
            $_SESSION['error'] = $valid['mensaje'];
            header('Location: /update-zavick');
            exit;
        }

        // Si pasa validación, proceder a actualizar
        try {
            $respuesta = $this->dPreguntaM->updatePregunta($id, $descripcion, $respuestas);

            if ($respuesta == true) {
                $_SESSION['mensaje'] = 'Pregunta actualizada correctamente.';
                header('Location: /update-zavick');
                exit;
            } else {
                $_SESSION['error'] = 'Error en el proceso de actualizar pregunta (modelo).';
                header('Location: /update-zavick');
                exit;
            }
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Error al actualizar la pregunta: ' . $e->getMessage();
            header('Location: /update-zavick');
            exit;
        }
    }

    /**
     * Valida el arreglo de respuestas (formulario zavick)
     * @param array $respuestas
     * @return array{error: bool, mensaje: string}
     */
    public function validateRespuestas(array $respuestas): array
    {
        // Validación: Verificar campos vacíos
        $errores = [];
        foreach ($respuestas as $index => $respuesta) {
            if (empty($respuesta[0])) $errores[] = "ID de respuesta " . ($index + 1) . " es requerido.";
            if (empty($respuesta[1])) $errores[] = "Descripción de respuesta " . ($index + 1) . " es requerida.";
            if (empty($respuesta[2])) $errores[] = "Valor de respuesta " . ($index + 1) . " es requerido.";
        }

        if (!empty($errores)) {
            return ['error' => true, 'mensaje' => implode(' - ', $errores)];
        }
        return ['error' => false, 'mensaje' => 'Sin errores.'];
    }

    /**
     * Elimina una pregunta y sus respuestas
     * @return void
     */
    public function delete(): void
    {
        if (empty($_POST['id_pregunta'])) {
            $_SESSION['error'] = 'No hay datos para eliminar la pregunta.';
            header('Location: /update-zavick');
            exit;
        }

        $respuestas = [
            $_POST['id_detpregunta1'] ?? '',
            $_POST['id_detpregunta2'] ?? '',
            $_POST['id_detpregunta3'] ?? '',
            $_POST['id_detpregunta4'] ?? '',
        ];

        try {
            $respuestadp = $this->dPreguntaM->deleteDetPreguntas($respuestas);

            if ($respuestadp == true) {
                if (!$this->preguntaM->deletePregunta($_POST['id_pregunta'])) {
                    $_SESSION['mensaje'] = 'Error en el proceso de eliminar pregunta (modelo)';
                    header('Location: /update-zavick');
                    exit;
                }
                $_SESSION['mensaje'] = 'Pregunta eliminada correctamente.';
                header('Location: /update-zavick');
                exit;
            } else {
                $_SESSION['error'] = 'Error en el proceso de eliminar respuestas (modelo).';
                header('Location: /update-zavick');
                exit;
            }
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Error al eliminar la respuesta: ' . $e->getMessage();
            header('Location: /update-zavick');
            exit;
        }
    }
}
