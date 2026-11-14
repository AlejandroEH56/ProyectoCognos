<?php
/**
 * @var array $perfiles
 * @var string $mensaje
 * @var string $error
 * @var string $anterior
 */
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perfiles profesionales</title>
    <link href="/Modules/FormularioPersona/Resources/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<?php include __DIR__ . '/../../../app/Views/header.php'; ?>
<main class="container mt-5">
    <h1>Perfiles profesionales</h1>
    <p>Registra los perfiles que podrás asignar al crear o editar una persona.</p>
    <?php if ($mensaje): ?>
        <div class="alert alert-success" role="status"><?= htmlspecialchars($mensaje, ENT_QUOTES, 'UTF-8') ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="alert alert-danger" role="alert"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
    <?php endif; unset($_SESSION['mensaje'], $_SESSION['error']); ?>
    <section class="card mb-4">
        <div class="card-body">
            <h2 class="h5">Nuevo perfil</h2>
            <form method="post" action="/crear-perfil-profesional" class="row g-3 align-items-end">
                <div class="col-md-9">
                    <label class="form-label" for="nuevo-perfil">Nombre del perfil</label>
                    <input class="form-control" id="nuevo-perfil" name="nombre_perfil" maxlength="64" required
                           value="<?= htmlspecialchars($anterior, ENT_QUOTES, 'UTF-8') ?>">
                </div>
                <div class="col-md-3"><button class="btn btn-primary" type="submit">Agregar perfil</button></div>
            </form>
        </div>
    </section>
    <h2 class="h4">Catálogo de perfiles</h2>
    <p>Un perfil inactivo conserva sus relaciones y deja de estar disponible para nuevas asignaciones y evaluaciones.</p>
    <?php if (!$perfiles): ?>
        <p class="alert alert-info">Aún no hay perfiles. Agrega el primero para registrar personas.</p>
    <?php endif; ?>
    <?php foreach ($perfiles as $perfil): ?>
        <form class="card card-body mb-3" method="post" action="/actualizar-perfil-profesional">
            <input type="hidden" name="id_perfil" value="<?= htmlspecialchars($perfil['id_perfil'], ENT_QUOTES, 'UTF-8') ?>">
            <div class="row g-3 align-items-end">
                <div class="col-md-7">
                    <label class="form-label" for="nombre-<?= htmlspecialchars($perfil['id_perfil'], ENT_QUOTES, 'UTF-8') ?>">Nombre del perfil</label>
                    <input class="form-control" name="nombre_perfil" maxlength="64" required
                           id="nombre-<?= htmlspecialchars($perfil['id_perfil'], ENT_QUOTES, 'UTF-8') ?>"
                           value="<?= htmlspecialchars($perfil['nombre_perfil'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label" for="estado-<?= htmlspecialchars($perfil['id_perfil'], ENT_QUOTES, 'UTF-8') ?>">Estado</label>
                    <select class="form-select" name="status" id="estado-<?= htmlspecialchars($perfil['id_perfil'], ENT_QUOTES, 'UTF-8') ?>">
                        <option value="1" <?= (int)$perfil['status'] === 1 ? 'selected' : '' ?>>Activo</option>
                        <option value="0" <?= (int)$perfil['status'] !== 1 ? 'selected' : '' ?>>Inactivo</option>
                    </select>
                </div>
                <div class="col-md-2"><button class="btn btn-success" type="submit">Guardar</button></div>
            </div>
        </form>
    <?php endforeach; ?>
    <a class="btn btn-outline-primary mt-2" href="/nueva-persona">Crear persona</a>
</main>
<?php include __DIR__ . '/../../../app/Views/footer.php'; ?>
</body>
</html>
