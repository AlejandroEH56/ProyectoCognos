<!-- Pregunta de opciones agrupadas -->
<!-- Input:
    var $preguntaId: ID para cada opcion
    var $nameGroup: Grupo de los radio (cada grupo es diferente)
    var $id1 y $id2: id persolalizado para cada radio
-->
<?php
// echo '<pre>';
// print_r($datosTest);
// echo '</pre>';
?>
<div class="row mb-3">
    <?php
    $contPreguntas = 1;
    for ($i = 0; $i < count($datosTest); $i += 2):
        $preguntaId = 'preg' . str_pad($contPreguntas, 3, '0', STR_PAD_LEFT);
        $nameGroup = 'respuesta_' . $preguntaId;
        $id1 = 'radio_' . $preguntaId . '_1';
        $id2 = 'radio_' . $preguntaId . '_2';
    ?>
        <div class="mb-4"> <!-- col-10 justify-content-center  -->
            <fieldset class="border p-3 rounded">
                <legend class="h6 ps-2 text-start"><?= htmlspecialchars($contPreguntas) ?>.-</legend>
                <div class="form-check mt-2 ms-2 text-start">
                    <input class="form-check-input" type="radio" name="<?= $nameGroup ?>" id="<?= $id1 ?>" value="<?= htmlspecialchars($datosTest[$i]['valor']) ?>" required>
                    <label class="col-12 form-check-label" for="<?= $id1 ?>">
                        <?= htmlspecialchars($datosTest[$i]['respuesta']) ?>
                    </label>
                </div>
                <div class="form-check mt-1 ms-2 text-start">
                    <input class="form-check-input" type="radio" name="<?= $nameGroup ?>" id="<?= $id2 ?>" value="<?= htmlspecialchars($datosTest[$i]['valor']) ?>">
                    <label class="col-12 form-check-label" for="<?= $id2 ?>">
                        <?= htmlspecialchars($datosTest[$i + 1]['respuesta']) ?>
                    </label>
                </div>
            </fieldset>
        </div>
    <?php
        $contPreguntas++;
    endfor;
    ?>
</div>