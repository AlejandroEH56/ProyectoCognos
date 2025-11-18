<!-- Pregunta de opciones agrupadas -->
<!-- Input:
    var $descripcion: Incluye la pregunta por responer
    var $nombre: Debe ser el mismo nombre para todos los valores (columnas)
        Nota: se usa como ID para cada input (e.g. liquidez1, solvencia2, etc.)
    var $grupo: Debe ser el mismo para todos los input
-->
<?php
if (!empty($datosTest)):
    // echo '<pre>';
    // print_r($datosTest);
    // echo '</pre>';
    $contPreguntas = 1;
    foreach ($datosTest as $test): ?>
        <div class="mb-4"> <!-- col-10 col-md-12 col-sm-12 -->
            <fieldset class="border p-3 rounded">
                <legend class="h6 text-start"><?= htmlspecialchars($contPreguntas . '. ' . $test['pregunta']) ?>:</legend>
                <div class="row">
                    <?php foreach ($test['opciones'] as $row): ?>
                        <div class="col-12">
                            <div class="d-flex justify-content-between">
                                <label class="form-label col-md-6 text-start"><?= htmlspecialchars($row['campo']) ?></label>
                                <!-- Genera cada uno de los inputs (valores de 1 a 4, o de acuerdo a la cantidad de preguntas) -->
                                <?php for ($i = 1; $i <= count($test['opciones']); $i++): ?>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input <?= htmlspecialchars($row['grupo']) ?>" type="radio"
                                            name="<?= htmlspecialchars($row['nombre']) ?>"
                                            id="<?= htmlspecialchars($row['nombre'] . $i) ?>" value="<?= $i ?>" required>
                                        <label class="form-check-label" for="<?= htmlspecialchars($row['nombre'] . $i) ?>"><?= $i ?></label>
                                    </div>
                                <?php endfor; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <div class="form-text text-danger" id="<?= htmlspecialchars($test['opciones'][0]['grupo']) ?>Error"></div>
            </fieldset>
        </div>
<?php $contPreguntas++;
    endforeach;
else:
    echo '<div class="col-10 mb-4"><p class= "text-danger">No hay preguntas para mostrar</p></div>';
endif; ?>