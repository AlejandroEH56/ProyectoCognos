<?php

/**
 * Datos compartidos por los formularios de evaluación.
 * @var array $anteriores
 * @var array $personas
 * @var array $perfiles
 * @var string $token
 */
$personaAnterior = is_string($anteriores['id_persona'] ?? null) ? $anteriores['id_persona'] : '';
$perfilAnterior = is_string($anteriores['id_perfil'] ?? null) ? $anteriores['id_perfil'] : '';
?>
<input type="hidden" name="evaluacion_token" value="<?= htmlspecialchars($token, ENT_QUOTES, 'UTF-8') ?>">
<?php if (!$personas || !$perfiles): ?>
    <p class="alert alert-warning">Para guardar la evaluación debe haber al menos una persona y un perfil activos registrados.</p>
<?php endif; ?>
<div class="row mb-4">
    <div class="col-md-6 mb-3">
        <label for="evaluacion-persona" class="form-label">Persona</label>
        <select id="evaluacion-persona" name="id_persona" class="form-select" required>
            <option value="">Selecciona una persona</option>
            <?php foreach ($personas as $persona): ?>
                <option value="<?= htmlspecialchars($persona['id_persona'], ENT_QUOTES, 'UTF-8') ?>" <?= $personaAnterior === $persona['id_persona'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars(trim(($persona['nombre'] ?? '') . ' ' . ($persona['apellido'] ?? '')), ENT_QUOTES, 'UTF-8') ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="col-md-6 mb-3">
        <label for="evaluacion-perfil" class="form-label">Perfil de la evaluación</label>
        <select id="evaluacion-perfil" name="id_perfil" class="form-select" required>
            <option value="">Selecciona un perfil</option>
            <?php foreach ($perfiles as $perfil): ?>
                <option value="<?= htmlspecialchars($perfil['id_perfil'], ENT_QUOTES, 'UTF-8') ?>" <?= $perfilAnterior === $perfil['id_perfil'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($perfil['nombre_perfil'] ?? '', ENT_QUOTES, 'UTF-8') ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
</div>