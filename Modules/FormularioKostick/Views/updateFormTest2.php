<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Formulario de Evaluación</title>
    <link href="Modules/FormularioKostick/Resources/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
</head>

<body>
    <?php include "App/Views/header.php"; ?>

    <div class="container mt-5 col-10">
        <div class="card">
            <div class="card-header">
                <div class="row d-flex justify-content-between align-items-end">
                    <div class="col-sm-6">
                        <h2 class="mb-4">Test 2: Kostick (Editar)</h2>
                    </div>
                    <div class="col-sm-auto d-flex gap-2">
                        <button type="submit" class="btn btn-primary" id="btnAddNewData" data-bs-toggle="modal" data-bs-target="#addNewDataModal"><i class="bi bi-plus-circle"></i> Nueva Pregunta</button>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <main>
                    <div class="row justify-content-center">
                        <div class="col-12 col-md-10 col-lg-8 mx-auto">
                            <!-- Mensajes de retroalimentacion -->
                            <?php if ($mensaje): ?>
                                <div class="alert alert-success alert-dismissible fade show" role="alert">
                                    <strong><?php echo addslashes($mensaje); ?></strong>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                </div>
                            <?php unset($_SESSION['mensaje']);
                            endif; ?>
                            <?php if ($error): ?>
                                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                    <strong><?php echo addslashes($error); ?></strong>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                </div>
                            <?php unset($_SESSION['error']);
                            endif; ?>
                            <div id="evaltest2">
                                <!-- Grupo de preguntas -->
                                <section class="mb-4">
                                    <?php $rendered->editFormView(); ?>
                                </section>
                                <div class="d-flex justify-content-end">
                                    <!-- <button type="reset" class="btn btn-light">Cancelar</button> -->
                                    <button type="submit" class="btn btn-primary" id="btnAddNewData" data-bs-toggle="modal" data-bs-target="#addNewDataModal">Nueva Pregunta</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </main>
            </div>
        </div>
    </div>

    <!-- Modal para Agregar Nueva Pregunta -->
    <div class="modal modal-lg fade" id="addNewDataModal" tabindex="-1" role="dialog" aria-labelledby="addNewDataModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header bg-primary">
                    <h5 class="modal-title text-light" id="addNewDataModalLabel">Nueva Pregunta</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="editForm" method="POST" action="/create-kostick">
                        <!-- Form Row-->
                        <div class="row gx-3">
                            <!-- Form Group (nombre)-->
                            <div class="col-md-9 mb-3">
                                <label class="small mb-1" for="descripcion1">Descripción</label>
                                <input class="form-control" id="descripcion1" type="text"
                                    placeholder="Ingresa la descripcion de la respuesta" name="descripcion1" required
                                    title="Ingrese los datos necesarios" />
                            </div>
                            <!-- Form Group (apellidos)-->
                            <div class="col-md-3 mb-3">
                                <label class="small mb-1" for="valor1">Valor</label>
                                <input class="form-control" id="valor1" type="text"
                                    inputmode="numeric" pattern="\d*" maxlength="10"
                                    placeholder="Valor de la respuesta" name="valor1" required
                                    title="Ingrese solo números"
                                    oninput="this.value = this.value.replace(/\D/g, '');" />
                            </div>
                            <!-- Form Group (nombre)-->
                            <div class="col-md-9 mb-3">
                                <label class="small mb-1" for="descripcion2">Descripción</label>
                                <input class="form-control" id="descripcion2" type="text"
                                    placeholder="Ingresa la descripcion de la respuesta" name="descripcion2" required
                                    title="Ingrese los datos necesarios" />
                            </div>
                            <!-- Form Group (apellidos)-->
                            <div class="col-md-3 mb-3">
                                <label class="small mb-1" for="valor2">Valor</label>
                                <input class="form-control" id="valor2" type="text"
                                    inputmode="numeric" pattern="\d*" maxlength="10"
                                    placeholder="Valor de la respuesta" name="valor2" required
                                    title="Ingrese solo números"
                                    oninput="this.value = this.value.replace(/\D/g, '');" />
                            </div>
                        </div>
                        <div class="modal-footer d-flex justify-content-between">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                            <button type="submit" class="btn btn-success">Guardar</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <!-- Modal para Eliminar Pregunta -->
    <div class="modal fade" id="deleteModalPregunta" tabindex="-1" role="dialog" aria-labelledby="deleteModalPreguntaLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header bg-danger">
                    <h5 class="modal-title text-light" id="deleteModalPreguntaLabel">Eliminar Pregunta</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div id="body-msg" class="modal-body">
                    ¿Estás seguro de que deseas eliminar esta Pregunta?
                </div>
                <div class="modal-footer">
                    <form id="deleteForm" method="POST" action="/delete-kostick">
                        <input type="hidden" name="id_detpregunta1" id="id_detpregunta1" value="">
                        <input type="hidden" name="id_detpregunta2" id="id_detpregunta2" value="">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cerrar</button>
                        <button type="submit" class="btn btn-danger">Eliminar</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <?php include 'App/Views/footer.php'; ?>

    <script src="Modules/FormularioKostick/Resources/js/bootstrap.bundle.min.js" type="text/javascript"></script>
    <script src="Modules/FormularioKostick/Resources/js/kostickModals.js" type="text/javascript"></script>

</body>

</html>