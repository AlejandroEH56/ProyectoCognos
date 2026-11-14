<?php
/**
 * @var array $perfiles
 * @var string $selectorId
 * @var string $perfilSeleccionado
 * @var bool $incluirInactivos
 */
?>
<label for="<?= htmlspecialchars($selectorId, ENT_QUOTES, 'UTF-8') ?>" class="small mb-1">Perfil profesional</label>
<select class="form-select" id="<?= htmlspecialchars($selectorId, ENT_QUOTES, 'UTF-8') ?>" name="id_perfil" required>
    <option value="">Selecciona un perfil profesional</option>
    <?php foreach ($perfiles as $perfil): ?>
        <?php if (!$incluirInactivos && (int)$perfil['status'] !== 1) continue; ?>
        <option value="<?= htmlspecialchars($perfil['id_perfil'], ENT_QUOTES, 'UTF-8') ?>"
            <?= $perfilSeleccionado === $perfil['id_perfil'] ? 'selected' : '' ?>>
            <?= htmlspecialchars($perfil['nombre_perfil'] ?? '', ENT_QUOTES, 'UTF-8') ?><?= (int)$perfil['status'] !== 1 ? ' (inactivo)' : '' ?>
        </option>
    <?php endforeach; ?>
</select>
<?php if (!array_filter($perfiles, fn($perfil) => (int)$perfil['status'] === 1)): ?>
    <p class="text-danger mt-2">No hay perfiles activos. <a href="/perfiles-profesionales">Agrega un perfil profesional</a> para asignarlo a una persona.</p>
<?php endif; ?>
