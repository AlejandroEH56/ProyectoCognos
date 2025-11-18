<?php

namespace Modules\FormularioKostick\Controllers;

use App\Controllers\BaseController;
use Modules\FormularioKostick\Models\DetPreguntaModel;

class KostickTest extends BaseController
{

    private $dpreguntaM;

    public function __construct()
    {
        $this->dpreguntaM = new DetPreguntaModel();
    }

    /**
     * Devuelve la vista de la evaluacion del test 2 (Kostick)
     * @return void
     */
    public function index()
    {
        $rendered = new KostickTest();
        $mensaje = $_SESSION['mensaje'] ?? '';
        $error = $_SESSION['error'] ?? '';
        $this->renderModule('formularioTest2', [
            'rendered' => $rendered,
            'mensaje' => $mensaje,
            'error' => $error
        ]);
    }

    /**
     * Devuelve la vista de la evaluacion del test 2 (Kostick) para editar
     * @return void
     */
    public function viewEditTest()
    {
        $rendered = new KostickTest();
        $mensaje = $_SESSION['mensaje'] ?? '';
        $error = $_SESSION['error'] ?? '';
        $this->renderModule('updateFormTest2', [
            'rendered' => $rendered,
            'mensaje' => $mensaje,
            'error' => $error
        ]);
    }

    /**
     * Renderiza las preguntas (Kostick)
     */
    public function formView()
    {
        $datosTest = $this->dpreguntaM->getEvalTest2();

        $mensaje = $_SESSION['mensaje'] ?? '';
        $error = $_SESSION['error'] ?? '';
        return $this->renderModule('campoTest2', [
            'mensaje' => $mensaje,
            'error' => $error,
            'datosTest' => $datosTest,
        ]);
    }

    /**
     * Renderiza las preguntas (Kostick) para editar
     */
    public function editFormView()
    {
        $datosTest = $this->dpreguntaM->getEvalTest2();

        $mensaje = $_SESSION['mensaje'] ?? '';
        $error = $_SESSION['error'] ?? '';
        return $this->renderModule('updateCampoTest2', [
            'mensaje' => $mensaje,
            'error' => $error,
            'datosTest' => $datosTest,
        ]);
    }

    /**
     * Crea una nueva pregunta (consecutivos par)
     * @return null
     */
    public function create()
    {
        if (!isset($_POST['descripcion1']) || !isset($_POST['descripcion2'])) {
            $_SESSION['error'] = "Respuesta y/o descripcion no proporcionada";
            header("Location: /update-kostick");
            return exit();
        }
        $ID = $this->getIdPreguntaKostick();

        if (empty($ID)) {
            $_SESSION['error'] = "Error al obtener el id pregunta";
            header("Location: /update-kostick");
            return exit();
        }

        $data1 = [
            'id_detpregunta' => $this->genPreguntaId(),
            'respuesta' => $_POST['descripcion1'],
            'id_pregunta' => $ID,
            'valor' => $_POST['valor1'],
        ];

        $val = $this->dpreguntaM->createDetPregunta($data1);
        if (!$val) {
            $_SESSION['error'] = "Error al guardar la pregunta (1). {$val}";
            header("Location: /update-kostick");
            return exit();
        }

        $data2 = [
            'id_detpregunta' => $this->genPreguntaId(),
            'respuesta' => $_POST['descripcion2'],
            'id_pregunta' => $ID,
            'valor' => $_POST['valor2'],
        ];

        $val2 = $this->dpreguntaM->createDetPregunta($data2);
        if (!$val2) {
            $_SESSION['error'] = "Error al guardar la pregunta (2).";
            header("Location: /update-kostick");
            return exit();
        }
        // Redirige o muestra un mensaje de éxito
        $_SESSION['mensaje'] = "Pregunta creada con exito.";
        header("Location: /update-kostick");
        return exit();
    }

    /**
     * Actualiza los datos de una pregunta (consecutivos par)
     * @return null
     */
    public function update()
    {
        if (!isset($_POST['id_detpregunta1']) || !isset($_POST['id_detpregunta2'])) {
            $_SESSION['error'] = "ID de respuesta no proporcionado";
            header("Location: /update-kostick");
            return exit();
        }

        $data1 = [
            'respuesta' => $_POST['respuesta1'],
            'valor' => $_POST['valor1'],
        ];

        $data2 = [
            'respuesta' => $_POST['respuesta2'],
            'valor' => $_POST['valor2'],
        ];

        if (!$this->dpreguntaM->updateDetPregunta($_POST['id_detpregunta1'], $data1) > 0) {
            $_SESSION['error'] = "Error al actualizar la respuesta (1) y/o sin cambios.";
            header("Location: /update-kostick");
            return exit();
        }

        if (!$this->dpreguntaM->updateDetPregunta($_POST['id_detpregunta2'], $data2) > 0) {
            $_SESSION['error'] = "Error al actualizar la respuesta (2) y/o sin cambios.";
            header("Location: /update-kostick");
            return exit();
        }


        $_SESSION['mensaje'] = "Respuesta(s) actualizadas correctamente.";
        header("Location: /update-kostick");
        return exit();
    }

    /**
     * Elimina una pregunta del test (kostick)
     * @return null
     */
    public function delete()
    {
        if (!isset($_POST['id_detpregunta1']) || !isset($_POST['id_detpregunta2'])) {
            $_SESSION['error'] = "ID de respuesta no proporcionado";
            header("Location: /update-kostick");
            return exit();
        }

        if (!$this->dpreguntaM->deleteDetPregunta($_POST['id_detpregunta1'])) {
            $_SESSION['error'] = "Error al eliminar la respuesta (1).";
            header("Location: /update-kostick");
            return exit();
        }

        if (!$this->dpreguntaM->deleteDetPregunta($_POST['id_detpregunta2'])) {
            $_SESSION['error'] = "Error al eliminar la respuesta (2).";
            header("Location: /update-kostick");
            return exit();
        }
        // Redirige o muestra un mensaje de éxito
        $_SESSION['mensaje'] = "Pregunta eliminada correctamente.";
        header("Location: /update-kostick");
        return exit();
    }

    /**
     * Genera el ID de la pregunta (detPregunta)
     * @param string $acronimo
     * @return string
     */
    public function genPreguntaId(string $acronimo = 'dpreg'): string
    {
        $numeroConsecutivo = $this->dpreguntaM->getRespuestaCount();
        // Formatear número con ceros a la izquierda (mínimo 3 dígitos) y formatear fecha y hora
        $numeroFormateado = str_pad($numeroConsecutivo['total'] + 1, 3, '0', STR_PAD_LEFT);
        $fechaHora = date('dmYHis');

        return "{$acronimo}-{$numeroFormateado}-{$fechaHora}";
    }

    /**
     * Devuelve el id de la pregunta (kostick)
     * @return string
     */
    public function getIdPreguntaKostick(): string|null
    {
        $valor = $this->dpreguntaM->getIDPreguntaKostick();
        return (string)$valor['ID'];
    }
}
