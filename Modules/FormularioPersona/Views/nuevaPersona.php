<?php

/**
 * Datos recibidos mediante BaseController::renderModule().
 * @var string $error
 * @var string $mensaje
 * @var array $perfiles
 * @var array $anteriores
 */
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Creacion Persona</title>
    <link href="/Modules/FormularioPersona/Resources/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <?php include __DIR__ . '/../../../app/Views/header.php'; ?>

    <div class="container mt-5 col-10">
        <div class="card">
            <div class="card-header">
                <h2 class="mb-4">Datos de la persona</h2>
            </div>
            <div class="card-body">
                <main>
                    <div class="row justify-content-center">
                        <div class="col-12 col-md-10 col-lg-8 mx-auto">
                            <!-- Envia la lista de errores al formulario -->
                            <div class="alert alert-success" role="alert">
                                <h4 class="alert-heading">Crear nueva persona</h4>
                                <p>Ingresa los datos solicitados.</p>
                            </div>
                            <!-- Mensajes de retroalimentacion -->
                            <?php if ($mensaje): ?>
                                <div class="alert alert-success alert-dismissible fade show" role="alert">
                                    <strong><?php echo htmlspecialchars($mensaje, ENT_QUOTES, 'UTF-8'); ?></strong>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                </div>
                            <?php unset($_SESSION['mensaje']);
                            endif; ?>
                            <?php if ($error): ?>
                                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                    <strong><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></strong>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                </div>
                            <?php unset($_SESSION['error']);
                            endif; ?>
                            <form id="evaluacionForm" action="/create-user" method="POST">
                                <!-- Form Row-->
                                <div class="row gx-3">
                                    <!-- Form Group (nombre)-->
                                    <div class="mb-3">
                                        <label class="small mb-1" for="nombre">Nombre(s)</label>
                                        <input class="form-control" id="nombre" type="text"
                                            placeholder="Ingresa el/los nombre(s)" name="nombre" required maxlength="64" value="<?= htmlspecialchars($anteriores['nombre'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                                            title="Ingrese los datos necesarios" />
                                    </div>
                                    <!-- Form Group (apellidos)-->
                                    <div class="mb-3">
                                        <label class="small mb-1" for="apellidos">Apellido(s)</label>
                                        <input class="form-control" id="apellidos" type="text"
                                            placeholder="Ingresa el/los apellido(s)" name="apellidos" required maxlength="64" value="<?= htmlspecialchars($anteriores['apellidos'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                                            title="Ingrese los datos necesarios" />
                                    </div>
                                </div>
                                <!-- Form Row-->
                                <div class="row gx-3">
                                    <div class="mb-3">
                                        <label for="cadena-curp" class="small mb-1">CURP</label>
                                        <input type="text" class="form-control" id="cadena-curp" name="curp" required value="<?= htmlspecialchars($anteriores['curp'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                                            placeholder="Ingresa el CURP"
                                            title="Debe ser una CURP válida en mayúsculas con 18 caracteres">
                                    </div>
                                </div>
                                <!-- Form Row-->
                                <div class="row gx-3">
                                    <div class="mb-3">
                                        <label for="cadena-rfc" class="small mb-1">RFC</label>
                                        <input type="text" class="form-control" id="cadena-rfc" name="rfc" required value="<?= htmlspecialchars($anteriores['rfc'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                                            placeholder="Ingresa el RFC"
                                            title="Debe ser una RFC válida en mayúsculas con 12 o 13 caracteres">
                                    </div>
                                </div>
                                <!-- Form Row-->
                                <div class="row gx-3">
                                    <div class="mb-3">
                                        <label for="nivel-academico" class="small mb-1">Nivel Academico</label>
                                        <input type="text" class="form-control" id="nivel-academico" name="nivel" required value="<?= htmlspecialchars($anteriores['nivel'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                                            placeholder="Ingresa el nivel academico"
                                            title="Ingrese los datos necesarios">
                                    </div>
                                </div>
                                <!-- Form Row-->
                                <div class="row gx-3">
                                    <div class="mb-3">
                                        <?php
                                        $selectorId = 'perfil';
                                        $perfilSeleccionado = $anteriores['id_perfil'] ?? '';
                                        $incluirInactivos = false;
                                        include __DIR__ . '/selectorPerfil.php';
                                        ?>
                                    </div>
                                </div>
                                <hr class="my-4" />
                                <div class="row justify-content-center">
                                    <div class="col-lg-10 col-md-6 pt-3 d-flex justify-content-between">
                                        <button type="reset" class="btn btn-light">Cancelar</button>
                                        <button type="submit" class="btn btn-success" <?= !$perfiles ? 'disabled' : '' ?>>Enviar</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </main>
            </div>
        </div>
    </div>

    <?php include __DIR__ . '/../../../app/Views/footer.php'; ?>

    <script src="/Modules/FormularioPersona/Resources/js/bootstrap.bundle.min.js" type="text/javascript"></script>

</body>

</html>
