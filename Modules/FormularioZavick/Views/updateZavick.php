<?php

/**
 * Datos recibidos mediante BaseController::renderModule().
 * @var string $error
 * @var string $mensaje
 * @var object $view
 */
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Formulario de Evaluación</title>
    <link href="/Modules/FormularioZavick/Resources/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
</head>

<body>
    <?php include __DIR__ . '/../../../app/Views/header.php'; ?>

    <div class="container mt-5 col-10">
        <div class="card">
            <div class="card-header">
                <div class="row d-flex justify-content-between align-items-end">
                    <div class="col-sm-6">
                        <h2 class="mb-4">Test 3: Zavic (Editar) </h2>
                    </div>
                    <div class="col-sm-auto d-flex gap-2">
                        <button type="button" class="btn btn-primary" id="btnAddNewPreg" data-bs-toggle="modal" data-bs-target="#newModalZavick"><i class="bi bi-plus-circle"></i> Nueva Pregunta</button>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <main>
                    <div class="row justify-content-center">
                        <div class="col-12 col-md-10 col-lg-10 mx-auto">
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
                            <!-- Grupo de preguntas -->
                            <section class="mb-4">
                                <?= $view->updateField(); ?>
                            </section>
                        </div>
                    </div>
                </main>
                <div class="d-flex justify-content-end">
                    <button type="button" class="btn btn-primary" id="btnAddNewPregBottom" data-bs-toggle="modal" data-bs-target="#newModalZavick"><i class="bi bi-plus-circle"></i> Nueva Pregunta</button>
                </div>
            </div>
        </div>
    </div>

    <?php include __DIR__ . '/modals_zavick.php'; ?>
    <?php include __DIR__ . '/../../../app/Views/footer.php'; ?>

    <script src="/Modules/FormularioZavick/Resources/js/zavickModals.js" type="text/javascript"></script>
    <script src="/Modules/FormularioZavick/Resources/js/bootstrap.bundle.min.js" type="text/javascript"></script>

</body>

</html>