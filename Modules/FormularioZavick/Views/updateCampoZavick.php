<!-- Pregunta de opciones agrupadas -->
<!-- Input:
    var $descripcion: Incluye la pregunta por responer
-->
<?php
if (!empty($datosTest)):
    // echo '<pre>';
    // print_r($datosTest);  // para pruebas
    // echo '</pre>';
    $contPreguntas = 1;
    foreach ($datosTest as $test): ?>
        <div class="mb-4">
            <fieldset class="border p-3 rounded">
                <form action="/update-zavick" method="POST">
                    <div class="row p-3">
                        <div class="col-12">
                            <input type="hidden" value="<?= htmlspecialchars($test['id_pregunta']) ?>" name="id_pregunta">
                            <label class="form-label" for="des_pregunta<?= $contPreguntas ?>">Descripcion pregunta No. <?= htmlspecialchars($contPreguntas) ?></label>
                            <input class="form-control" value="<?= htmlspecialchars($test['pregunta']) ?>" name="des_pregunta" id="des_pregunta<?= $contPreguntas ?>">
                        </div>
                        <?php foreach ($test['opciones'] as $id => $row): ?>
                            <div class="col-9">
                                <input type="hidden" value="<?= htmlspecialchars($row['idRes']) ?>" name="id_respuesta<?= $id + 1 ?>">
                                <label class="ms-2 pt-2" for="des_respuesta<?= $id + 1 ?>">Descripcion respuesta no. <?= htmlspecialchars($id + 1) ?></label>
                                <input class="form-control" type="text" value="<?= htmlspecialchars($row['campo']) ?>"
                                    name="des_respuesta<?= $id + 1 ?>" id="des_respuesta<?= $id + 1 ?>" required>
                            </div>
                            <div class="col-3">
                                <label class="ms-2 pt-2" for="des_valor<?= $id + 1 ?>">Valor</label>
                                <input class="form-control" type="text" value="<?= htmlspecialchars($row['valor']) ?>"
                                    name="des_valor<?= $id + 1 ?>" id="des_valor<?= $id + 1 ?>" required>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="d-flex justify-content-between mt-4">
                        <button type="button" class="btn btn-danger ms-3" data-bs-toggle="modal" data-bs-target="#deleteModalPregunta"
                            data-pregunta="<?= htmlspecialchars(json_encode($test, JSON_HEX_APOS | JSON_HEX_QUOT)) ?>">
                            <i class="bi bi-trash"></i> Eliminar
                        </button>
                        <button type="submit" class="btn btn-success"><i class="bi bi-pencil-square"></i> Guardar</button>
                    </div>
                </form>
            </fieldset>
        </div>
<?php $contPreguntas++;
    endforeach;
else:
    echo '<div class="col-10 mb-4"><p class= "text-danger">No hay preguntas para mostrar</p></div>';
endif; ?>