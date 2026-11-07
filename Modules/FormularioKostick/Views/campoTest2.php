<?php

/**
 * Datos recibidos mediante BaseController::renderModule().
 * @var array $datosTest
 * @var array $anteriores
 */
?>
<!-- Pregunta de opciones agrupadas -->
<div class="row mb-3">
    <?php
    $contPreguntas = 1;
    $anteriores = is_array($anteriores ?? null) ? $anteriores : [];
    for ($i = 0; $i + 1 < count($datosTest); $i += 2):
        $preguntaId = 'preg' . str_pad($contPreguntas, 3, '0', STR_PAD_LEFT);
        $nameGroup = 'respuestas[' . $preguntaId . ']';
        $id1 = 'radio_' . $preguntaId . '_1';
        $id2 = 'radio_' . $preguntaId . '_2';
    ?>
        <div class="mb-4"> <!-- col-10 justify-content-center  -->
            <fieldset class="border p-3 rounded">
                <legend class="h6 ps-2 text-start"><?= htmlspecialchars($contPreguntas) ?>.-</legend>
                <div class="form-check mt-2 ms-2 text-start">
                    <input class="form-check-input" type="radio" name="<?= $nameGroup ?>" id="<?= $id1 ?>" value="<?= htmlspecialchars($datosTest[$i]['id_detpregunta'], ENT_QUOTES, 'UTF-8') ?>" <?= ($anteriores[$preguntaId] ?? null) === $datosTest[$i]['id_detpregunta'] ? 'checked' : '' ?> required>
                    <label class="col-12 form-check-label" for="<?= $id1 ?>">
                        <?= htmlspecialchars($datosTest[$i]['respuesta'] ?? '') ?>
                    </label>
                </div>
                <div class="form-check mt-1 ms-2 text-start">
                    <input class="form-check-input" type="radio" name="<?= $nameGroup ?>" id="<?= $id2 ?>" value="<?= htmlspecialchars($datosTest[$i + 1]['id_detpregunta'], ENT_QUOTES, 'UTF-8') ?>" <?= ($anteriores[$preguntaId] ?? null) === $datosTest[$i + 1]['id_detpregunta'] ? 'checked' : '' ?> required>
                    <label class="col-12 form-check-label" for="<?= $id2 ?>">
                        <?= htmlspecialchars($datosTest[$i + 1]['respuesta'] ?? '') ?>
                    </label>
                </div>
            </fieldset>
        </div>
    <?php
        $contPreguntas++;
    endfor;
    ?>
</div>
<?php if (!$datosTest || count($datosTest) % 2 !== 0): ?>
    <p class="text-danger">El catálogo de Kostick debe contener pares completos de opciones para responder.</p>
<?php endif; ?>