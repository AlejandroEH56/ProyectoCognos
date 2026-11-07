<?php

/**
 * Datos recibidos mediante BaseController::renderModule().
 * @var array $datosTest
 * @var array $anteriores
 */
/* Pregunta de opciones agrupadas */

$anteriores = is_array($anteriores ?? null) ? $anteriores : [];
if (!empty($datosTest)):
    $contPreguntas = 1;
    foreach ($datosTest as $test): ?>
        <div class="mb-4"> <!-- col-10 col-md-12 col-sm-12 -->
            <fieldset class="border p-3 rounded">
                <legend class="h6 text-start"><?= htmlspecialchars($contPreguntas . '. ' . ($test['pregunta'] ?? '')) ?>:</legend>
                <div class="row">
                    <?php foreach ($test['opciones'] as $row): ?>
                        <div class="col-12">
                            <div class="d-flex justify-content-between">
                                <label class="form-label col-md-6 text-start"><?= htmlspecialchars($row['campo'] ?? '') ?></label>
                                <!-- Genera cada uno de los inputs (valores de 1 a 4, o de acuerdo a la cantidad de preguntas) -->
                                <?php for ($i = 1; $i <= 4; $i++):
                                    $radioId = 'opcion_' . $row['idRes'] . '_' . $i;
                                ?>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input <?= htmlspecialchars($row['grupo']) ?>" type="radio"
                                            name="<?= htmlspecialchars($row['nombre']) ?>"
                                            id="<?= htmlspecialchars($radioId, ENT_QUOTES, 'UTF-8') ?>" value="<?= $i ?>" <?= ($anteriores[$row['idRes']] ?? null) === (string)$i ? 'checked' : '' ?> required>
                                        <label class="form-check-label" for="<?= htmlspecialchars($radioId, ENT_QUOTES, 'UTF-8') ?>"><?= $i ?></label>
                                    </div>
                                <?php endfor; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <?php if (count($test['opciones']) === 4): ?>
                    <div class="form-text text-danger" id="<?= htmlspecialchars($test['opciones'][0]['grupo']) ?>Error" aria-live="polite"></div>
                <?php else: ?>
                    <p class="text-danger">Esta pregunta debe tener cuatro opciones antes de responderla.</p>
                <?php endif; ?>
            </fieldset>
        </div>
<?php $contPreguntas++;
    endforeach;
else:
    echo '<div class="col-10 mb-4"><p class= "text-danger">No hay preguntas para mostrar</p></div>';
endif; ?>