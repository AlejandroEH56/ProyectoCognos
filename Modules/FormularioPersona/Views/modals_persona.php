<!-- Modal para Nueva Persona -->
<div class="modal modal-lg fade" id="newModalUser" tabindex="-1" role="dialog" aria-labelledby="newModalUserLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary">
                <h5 class="modal-title text-light" id="newModalUserLabel">Crear Persona</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="createForm" method="POST" action="/create-user">
                    <!-- Form Row-->
                    <div class="row gx-3">
                        <!-- Form Group (nombre)-->
                        <div class="col-md-6 mb-3">
                            <label class="small mb-1" for="new-nombre">Nombre(s)</label>
                            <input class="form-control" id="new-nombre" type="text"
                                placeholder="Ingresa el/los nombre(s)" name="nombre" required
                                title="Ingrese los datos necesarios" />
                        </div>
                        <!-- Form Group (apellidos)-->
                        <div class="col-md-6 mb-3">
                            <label class="small mb-1" for="new-apellidos">Apellido(s)</label>
                            <input class="form-control" id="new-apellidos" type="text"
                                placeholder="Ingresa el/los apellido(s)" name="apellidos" required
                                title="Ingrese los datos necesarios" />
                        </div>
                    </div>
                    <!-- Form Row-->
                    <div class="row gx-3">
                        <div class="col-md-8 mb-3">
                            <label for="new-curp" class="small mb-1">CURP</label>
                            <input type="text" class="form-control" id="new-curp" name="curp" required
                                placeholder="Ingresa el CURP"
                                title="Debe ser una CURP válida en mayúsculas con 18 caracteres">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="new-rfc" class="small mb-1">RFC</label>
                            <input type="text" class="form-control" id="new-rfc" name="rfc" required
                                placeholder="Ingresa el RFC"
                                title="Debe ser una RFC válida en mayúsculas con 12 o 13 caracteres">
                        </div>
                    </div>
                    <!-- Form Row-->
                    <div class="row gx-3">
                        <div class="mb-3">
                            <label for="new-nivel" class="small mb-1">Nivel Academico</label>
                            <input type="text" class="form-control" id="new-nivel" name="nivel" required
                                placeholder="Ingresa el nivel academico"
                                title="Ingrese los datos necesarios">
                        </div>
                    </div>
                    <!-- Form Row-->
                    <div class="row gx-3">
                        <div class="mb-3">
                            <label for="new-perfil" class="small mb-1">Perfil Profesional</label>
                            <input type="text" class="form-control" id="new-perfil" name="perfil" required
                                placeholder="Ingresa el perfil profesional"
                                title="Ingrese los datos necesarios">
                        </div>
                    </div>
                    <div class="modal-footer d-flex justify-content-between">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-success">Guardar Cambios</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<!-- Modal para Editar Persona -->
<div class="modal modal-lg fade" id="editModalUser" tabindex="-1" role="dialog" aria-labelledby="editModalUserLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-success">
                <h5 class="modal-title text-light" id="editModalUserLabel">Editar Persona</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="editForm" method="POST" action="/update-user">
                    <input type="hidden" name="user-id" id="usr-Id" />
                    <!-- Form Row-->
                    <div class="row gx-3">
                        <!-- Form Group (nombre)-->
                        <div class="col-md-6 mb-3">
                            <label class="small mb-1" for="usr-nombre">Nombre(s)</label>
                            <input class="form-control" id="usr-nombre" type="text"
                                placeholder="Ingresa el/los nombre(s)" name="nombre" required
                                title="Ingrese los datos necesarios" />
                        </div>
                        <!-- Form Group (apellidos)-->
                        <div class="col-md-6 mb-3">
                            <label class="small mb-1" for="usr-apellidos">Apellido(s)</label>
                            <input class="form-control" id="usr-apellidos" type="text"
                                placeholder="Ingresa el/los apellido(s)" name="apellidos" required
                                title="Ingrese los datos necesarios" />
                        </div>
                    </div>
                    <!-- Form Row-->
                    <div class="row gx-3">
                        <div class="col-md-8 mb-3">
                            <label for="usr-curp" class="small mb-1">CURP</label>
                            <input type="text" class="form-control" id="usr-curp" name="curp" required
                                placeholder="Ingresa el CURP"
                                title="Debe ser una CURP válida en mayúsculas con 18 caracteres">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="usr-rfc" class="small mb-1">RFC</label>
                            <input type="text" class="form-control" id="usr-rfc" name="rfc" required
                                placeholder="Ingresa el RFC"
                                title="Debe ser una RFC válida en mayúsculas con 12 o 13 caracteres">
                        </div>
                    </div>
                    <!-- Form Row-->
                    <div class="row gx-3">
                        <div class="mb-3">
                            <label for="usr-nivel" class="small mb-1">Nivel Academico</label>
                            <input type="text" class="form-control" id="usr-nivel" name="nivel" required
                                placeholder="Ingresa el nivel academico"
                                title="Ingrese los datos necesarios">
                        </div>
                    </div>
                    <!-- Form Row-->
                    <div class="row gx-3">
                        <div class="mb-3">
                            <label for="usr-perfil" class="small mb-1">Perfil Profesional</label>
                            <input type="text" class="form-control" id="usr-perfil" name="perfil" required
                                placeholder="Ingresa el perfil profesional"
                                title="Ingrese los datos necesarios">
                        </div>
                    </div>
                    <div class="modal-footer d-flex justify-content-between">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-success">Guardar Cambios</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<!-- Modal para Eliminar Persona -->
<div class="modal fade" id="deleteModalUser" tabindex="-1" role="dialog" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-danger">
                <h5 class="modal-title text-light" id="deleteModalUserLabel">Eliminar Persona</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div id="body-msg" class="modal-body">
                ¿Estás seguro de que deseas eliminar esta Persona?
            </div>
            <div class="modal-footer">
                <form id="deleteForm" method="POST" action="/delete-user">
                    <input type="hidden" name="user-id" id="deleteUserId" value="">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cerrar</button>
                    <button type="submit" class="btn btn-danger">Eliminar</button>
                </form>
            </div>
        </div>
    </div>
</div>