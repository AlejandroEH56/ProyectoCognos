<?php

/**
 * Datos recibidos mediante BaseController::renderModule().
 * @var array $datosTest
 */
?>
<!-- Pregunta de opciones agrupadas -->
<div class="row mb-3">
    <?php
    $contPreguntas = 1;
    for ($i = 0; $i + 1 < count($datosTest); $i += 2):
        $preguntaId = 'preg' . str_pad($contPreguntas, 3, '0', STR_PAD_LEFT);
        //$nameGroup = 'respuesta_' . $preguntaId;
        $id1 = 'val_' . $preguntaId . '_1';
        $id1D = 'des_' . $preguntaId . '_1';
        $id2 = 'val_' . $preguntaId . '_2';
        $id2D = 'des_' . $preguntaId . '_2';
    ?>
        <fieldset class="border p-3 rounded mb-4">
            <form method="POST" action="/update-kostick">
                <legend class="h6 ps-2 text-start">Pregunta No. <?= htmlspecialchars($contPreguntas) ?></legend>
                <div class="row mt-2 ms-2">
                    <div class="col-md-10">
                        <label class="mb-1" for="<?= $id1D ?>">Descripcion Respuesta 1</label>
                        <input type="hidden" value="<?= htmlspecialchars($datosTest[$i]['id_detpregunta']) ?>" name="id_detpregunta1">
                        <input class="form-control col-6" type="text" value="<?= htmlspecialchars($datosTest[$i]['respuesta'] ?? '') ?>" name="respuesta1" id="<?= $id1D ?>" placeholder="Introduca Respuesta" required>
                    </div>
                    <div class="col-md-2">
                        <label class="mb-1" for="<?= $id1 ?>">Valor</label>
                        <input class="form-control col-6" type="number" step="1" min="-2147483648" max="2147483647" id="<?= $id1 ?>" value="<?= htmlspecialchars((string)($datosTest[$i]['valor'] ?? '')) ?>" name="valor1" required>
                    </div>
                </div>
                <div class="row mt-1 ms-2">
                    <div class="col-md-10">
                        <label class="mb-1" for="<?= $id2D ?>">Descripcion Respuesta 2</label>
                        <input type="hidden" value="<?= htmlspecialchars($datosTest[$i + 1]['id_detpregunta']) ?>" name="id_detpregunta2">
                        <input class="form-control col-6" type="text" value="<?= htmlspecialchars($datosTest[$i + 1]['respuesta'] ?? '') ?>" name="respuesta2" id="<?= $id2D ?>" placeholder="Introduca Respuesta" required>
                    </div>
                    <div class="col-md-2">
                        <label class="mb-1" for="<?= $id2 ?>">Valor</label>
                        <input class="form-control col-6" type="number" step="1" min="-2147483648" max="2147483647" id="<?= $id2 ?>" value="<?= htmlspecialchars((string)($datosTest[$i + 1]['valor'] ?? '')) ?>" name="valor2" required>
                    </div>
                </div>
                <div class="d-flex justify-content-between mt-3">
                    <button type="button" class="btn btn-danger ms-3" data-bs-toggle="modal" data-bs-target="#deleteModalPregunta"
                        data-preg-id1="<?= htmlspecialchars($datosTest[$i]['id_detpregunta']) ?>"
                        data-preg-dat1="<?= htmlspecialchars((string)($datosTest[$i]['respuesta'] ?? '')) ?>"
                        data-preg-id2="<?= htmlspecialchars($datosTest[$i + 1]['id_detpregunta']) ?>"
                        data-preg-dat2="<?= htmlspecialchars((string)($datosTest[$i + 1]['respuesta'] ?? '')) ?>">
                        <i class="bi bi-trash"></i> Eliminar
                    </button>
                    <button type="submit" class="btn btn-success ms-3"><i class="bi bi-pencil-square"></i> Guardar</button>
                </div>
            </form>
        </fieldset>
    <?php
        $contPreguntas++;
    endfor;
    ?>
</div>