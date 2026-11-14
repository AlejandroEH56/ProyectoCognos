<?php

/**
 * Datos recibidos mediante BaseController::renderModule().
 * @var string $error
 * @var string $mensaje
 * @var object $rendered
 * @var array $personas
 * @var array $anteriores
 * @var string $token
 */
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Formulario de Evaluación</title>
    <link href="/Modules/FormularioKostick/Resources/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <?php include __DIR__ . '/../../../app/Views/header.php'; ?>

    <div class="container mt-5 col-10">
        <div class="card">
            <div class="card-header">
                <h2 class="mb-4">Test 2: Kostick</h2>
            </div>
            <div class="card-body">
                <main>
                    <div class="row justify-content-center">
                        <div class="col-12 col-md-10 col-lg-8 mx-auto">
                            <!-- Envia la lista de errores al formulario -->
                            <div class="alert alert-success" role="alert">
                                <h4 class="alert-heading">Preguntas de Evaluación</h4>
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
                            <form id="evaltest2" method="POST" action="/guardar-evaluacion-kostick">
                                <?php include __DIR__ . '/../../Evaluaciones/Views/evaluacionContexto.php'; ?>
                                <!-- Grupo de preguntas -->
                                <section class="mb-4">
                                    <?= $rendered->formView(); ?>
                                </section>

                                <div class="d-flex justify-content-between">
                                    <button type="reset" class="btn btn-light">Cancelar</button>
                                    <button type="submit" class="btn btn-success">Enviar</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </main>
            </div>
        </div>
    </div>

    <?php include __DIR__ . '/../../../app/Views/footer.php'; ?>

    <script src="/Modules/FormularioKostick/Resources/js/bootstrap.bundle.min.js" type="text/javascript"></script>

</body>

</html>
