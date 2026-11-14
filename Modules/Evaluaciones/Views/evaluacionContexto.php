<?php

/**
 * Datos compartidos por los formularios del módulo de evaluaciones.
 * @var array $anteriores
 * @var array $personas
 * @var string $token
 */
$personaAnterior = is_string($anteriores['id_persona'] ?? null) ? $anteriores['id_persona'] : '';
$personaSeleccionada = array_column($personas, null, 'id_persona')[$personaAnterior] ?? null;
$perfilMostrado = $personaSeleccionada['nombre_perfil'] ?? '';
if ($perfilMostrado !== '' && (int)$personaSeleccionada['perfil_status'] !== 1) {
    $perfilMostrado .= ' (inactivo)';
}
?>
<input type="hidden" name="evaluacion_token" value="<?= htmlspecialchars($token, ENT_QUOTES, 'UTF-8') ?>">
<?php if (!array_filter($personas, fn($persona) => $persona['id_perfil'] !== null && (int)$persona['perfil_status'] === 1)): ?>
    <p class="alert alert-warning">Para guardar la evaluación debe haber una persona activa con un perfil profesional activo asignado. <a href="/">Revisa las personas</a> o <a href="/perfiles-profesionales">registra un perfil</a>.</p>
<?php endif; ?>
<div class="row mb-4">
    <div class="col-md-6 mb-3">
        <label for="evaluacion-persona" class="form-label">Persona</label>
        <select id="evaluacion-persona" name="id_persona" class="form-select" required>
            <option value="">Selecciona una persona</option>
            <?php foreach ($personas as $persona): ?>
                <option value="<?= htmlspecialchars($persona['id_persona'], ENT_QUOTES, 'UTF-8') ?>"
                    data-perfil="<?= htmlspecialchars(($persona['nombre_perfil'] ?? 'Sin perfil asignado') . ((int)$persona['perfil_status'] !== 1 && $persona['id_perfil'] !== null ? ' (inactivo)' : ''), ENT_QUOTES, 'UTF-8') ?>"
                    <?= $personaAnterior === $persona['id_persona'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars(trim(($persona['nombre'] ?? '') . ' ' . ($persona['apellido'] ?? '')), ENT_QUOTES, 'UTF-8') ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="col-md-6 mb-3">
        <label for="evaluacion-perfil" class="form-label">Perfil profesional de la persona</label>
        <input id="evaluacion-perfil" class="form-control" type="text" readonly
            value="<?= htmlspecialchars($personaAnterior !== '' ? ($perfilMostrado ?: 'Sin perfil asignado') : 'Selecciona una persona', ENT_QUOTES, 'UTF-8') ?>"
            aria-describedby="perfil-evaluacion-ayuda">
        <p id="perfil-evaluacion-ayuda" class="form-text">La evaluación utiliza el perfil asignado a la persona. Puedes cambiarlo desde su ficha antes de evaluar.</p>
        <noscript>
            <p>Al enviar se utilizará el perfil que la persona tiene registrado.</p>
        </noscript>
    </div>
</div>
<script src="/Modules/Evaluaciones/Resources/js/evaluacionContexto.js" defer></script>