<?php

namespace Modules\Evaluaciones\Controllers;

use App\Controllers\BaseController;
use Modules\Evaluaciones\Models\EvaluacionModel;

/**
 * Flujo HTTP compartido de Kostick y Zavick sobre el controlador general.
 */
abstract class EvaluacionController extends BaseController
{
    /**
     * Prepara personas, token y selecciones anteriores para una evaluación.
     *
     * Entrada de sesión: evaluacion_anterior[test]. Registra el token y la huella del catálogo
     * en formularios_evaluacion para validar el envío posterior.
     *
     * @param string $test Nombre del test registrado, por ejemplo Kostick o Zavick.
     * @return array{personas: list<array<string, mixed>>, token: string, anteriores: array<string, mixed>} Datos que consumen las vistas.
     * @throws \Exception Si falla la lectura del catálogo, la conexión o la generación del token.
     */
    protected function evaluationData(string $test): array
    {
        $model = new EvaluacionModel();
        $token = bin2hex(random_bytes(16));
        $_SESSION['formularios_evaluacion'][$token] = ['test' => $test, 'catalogo' => $model->catalogHash($test)];
        return [
            'personas' => $model->getPersonas(), 'token' => $token,
            'anteriores' => $_SESSION['evaluacion_anterior'][$test] ?? []
        ];
    }

    /**
     * Valida el envío HTTP y solicita guardar la evaluación del test indicado.
     *
     * Entrada POST: evaluacion_token, id_persona y respuestas. Comprueba el token en sesión;
     * el modelo obtiene el perfil desde la persona. En caso de error conserva las selecciones.
     *
     * @param string $test Nombre del test al que debe pertenecer el formulario de sesión.
     * @param string $route Ruta del formulario a la que se redirige al terminar.
     * @return never Redirige con éxito o error y termina la ejecución.
     */
    protected function submitEvaluation(string $test, string $route): void
    {
        try {
            $token = $this->postString('evaluacion_token', 'formulario');
            $form = $_SESSION['formularios_evaluacion'][$token] ?? null;
            if (!$form || $form['test'] !== $test || !preg_match('/^[a-f0-9]{32}$/', $token)) {
                throw new \InvalidArgumentException('El formulario expiró. Recarga la evaluación antes de enviarla.');
            }
            $responses = $_POST['respuestas'] ?? null;
            if (!is_array($responses)) {
                throw new \InvalidArgumentException('Responde el cuestionario antes de enviarlo.');
            }
            $id = (new EvaluacionModel())->guardar(
                $test, $this->postString('id_persona', 'persona'),
                $responses, $token, $form['catalogo']
            );
            unset($_SESSION['evaluacion_anterior'][$test]);
            $this->redirectMessage($route, 'Evaluación guardada correctamente. Aplicación ' . $id . '.');
        } catch (\Throwable $error) {
            $_SESSION['evaluacion_anterior'][$test] = array_intersect_key($_POST, array_flip(['id_persona', 'respuestas']));
            $this->redirectError($route, $error);
        }
    }
}
