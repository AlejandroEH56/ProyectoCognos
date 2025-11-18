<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lista de personas</title>
    <!-- Hojas de estilo -->
    <link href="https://cdn.jsdelivr.net/npm/simple-datatables@latest/dist/style.css" rel="stylesheet" type="text/css">
    <link href="Modules/FormularioPersona/Resources/css/bootstrap.min.css" rel="stylesheet" type="text/css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
</head>

<body>
    <?php include 'App/Views/header.php'; ?>
    <?php include 'modals_persona.php'; ?>

    <div class="container mt-5 col-10">
        <div class="card">
            <div class="card-header">
                <div class="row d-flex justify-content-between align-items-end">
                    <div class="col-sm-6">
                        <h2 class="mb-4">Lista de personas</h2>
                    </div>
                    <div class="col-sm-auto d-flex gap-2">
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#newModalUser"><i class="bi bi-plus-circle"></i> Nuevo</button>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-12">
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
                        <div class="table-responsive">
                            <table class="table-responsive table-hover align-middle" id="datatablesSimple">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Nombre</th>
                                        <th>CURP</th>
                                        <th>RFC</th>
                                        <th>Nivel Academico</th>
                                        <th>Perfil Profesional</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($usuarios)): ?>
                                        <tr>
                                            <td colspan='7' class='text-center'>No hay datos disponibles.</td>
                                        </tr>
                                        <?php else:
                                        foreach ($usuarios as $row) { ?>
                                            <tr>
                                                <td><?= htmlspecialchars($row['ID']) ?></td>
                                                <td><?= htmlspecialchars($row['nombre'] . ' ' . $row['apellido'] ?? '') ?></td>
                                                <td><?= htmlspecialchars($row['curp'] ?? '') ?></td>
                                                <td><?= htmlspecialchars($row['rfc'] ?? '') ?></td>
                                                <td><?= htmlspecialchars($row['nivel_academico'] ?? '') ?></td>
                                                <td><?= htmlspecialchars($row['perfil_profesional'] ?? '') ?></td>
                                                <td style="width: 16%;">
                                                    <div class="d-inline-block">
                                                        <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#editModalUser" data-user="<?= htmlspecialchars(json_encode($row, JSON_HEX_APOS | JSON_HEX_QUOT)) ?>">
                                                            <i class='bi bi-pencil-square'></i> Editar
                                                        </button>
                                                        <button type="button" class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#deleteModalUser" data-user-id="<?= htmlspecialchars($row['ID']) ?>" data-user-name="<?= htmlspecialchars($row['nombre'] . ' ' . $row['apellido']) ?>">
                                                            <i class='bi bi-trash'></i> Eliminar
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                    <?php   }
                                    endif;
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include 'App/Views/footer.php'; ?>

    <script src="Modules/FormularioPersona/Resources/js/personaModals.js" type="text/javascript"></script>
    <script src="https://cdn.jsdelivr.net/npm/simple-datatables@latest" type="text/javascript"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js" integrity="sha384-ndDqU0Gzau9qJ1lfW4pNLlhNTkCfHzAVBReH9diLvGRem5+R9g2FzA8ZGN954O5Q" crossorigin="anonymous"></script>

</body>

</html>