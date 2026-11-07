<!-- Modal para Nueva Persona -->
<div class="modal modal-lg fade" id="newModalZavick" tabindex="-1" role="dialog" aria-labelledby="newModalZavickLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary">
                <h5 class="modal-title text-light" id="newModalZavickLabel">Nueva Pregunta</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="createForm" method="POST" action="/create-zavick">
                    <!-- Form Row-->
                    <div class="row gx-3">
                        <!-- Form Group (nombre)-->
                        <div class="col-md-12 mb-3">
                            <label class="small mb-1" for="des_pregunta">Descripción pregunta</label>
                            <input class="form-control" id="des_pregunta" type="text"
                                placeholder="Ingresa la descripción de la pregunta" name="des_pregunta" required
                                title="Ingrese los datos solicitados" />
                        </div>
                    </div>
                    <!-- Form Row (Respuesta 1)-->
                    <div class="row gx-3">
                        <div class="col-md-9 mb-3">
                            <label for="des_respuesta1" class="small mb-1">Descripción respuesta 1</label>
                            <input type="text" class="form-control" id="des_respuesta1" name="des_respuesta1" required
                                placeholder="Ingresa la descripción de la respuesta"
                                title="Ingresa los datos solicitados">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="des_valor1" class="small mb-1">Valor respueta 1</label>
                            <input type="number" step="1" min="-2147483648" max="2147483647" class="form-control" id="des_valor1" name="des_valor1" required
                                placeholder="Ingresa el valor"
                                title="Debe ser un valor númerico">
                        </div>
                    </div>
                    <!-- Form Row (Respuesta 2)-->
                    <div class="row gx-3">
                        <div class="col-md-9 mb-3">
                            <label for="des_respuesta2" class="small mb-1">Descripción respuesta 2</label>
                            <input type="text" class="form-control" id="des_respuesta2" name="des_respuesta2" required
                                placeholder="Ingresa la descripción de la respuesta"
                                title="Ingresa los datos solicitados">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="des_valor2" class="small mb-1">Valor respueta 2</label>
                            <input type="number" step="1" min="-2147483648" max="2147483647" class="form-control" id="des_valor2" name="des_valor2" required
                                placeholder="Ingresa el valor"
                                title="Debe ser un valor númerico">
                        </div>
                    </div>
                    <!-- Form Row (Respuesta 3)-->
                    <div class="row gx-3">
                        <div class="col-md-9 mb-3">
                            <label for="des_respuesta3" class="small mb-1">Descripción respuesta 3</label>
                            <input type="text" class="form-control" id="des_respuesta3" name="des_respuesta3" required
                                placeholder="Ingresa la descripción de la respuesta"
                                title="Ingresa los datos solicitados">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="des_valor3" class="small mb-1">Valor respueta 3</label>
                            <input type="number" step="1" min="-2147483648" max="2147483647" class="form-control" id="des_valor3" name="des_valor3" required
                                placeholder="Ingresa el valor"
                                title="Debe ser un valor númerico">
                        </div>
                    </div>
                    <!-- Form Row (Respuesta 4)-->
                    <div class="row gx-3">
                        <div class="col-md-9 mb-3">
                            <label for="des_respuesta4" class="small mb-1">Descripción respuesta 4</label>
                            <input type="text" class="form-control" id="des_respuesta4" name="des_respuesta4" required
                                placeholder="Ingresa la descripción de la respuesta"
                                title="Ingresa los datos solicitados">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="des_valor4" class="small mb-1">Valor respueta 4</label>
                            <input type="number" step="1" min="-2147483648" max="2147483647" class="form-control" id="des_valor4" name="des_valor4" required
                                placeholder="Ingresa el valor"
                                title="Debe ser un valor númerico">
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
                <form id="deleteForm" method="POST" action="/delete-zavick">
                    <input type="hidden" name="id_pregunta" id="udp-id_pregunta" value="">
                    <input type="hidden" name="id_detpregunta1" id="udp-id_detpregunta1" value="">
                    <input type="hidden" name="id_detpregunta2" id="udp-id_detpregunta2" value="">
                    <input type="hidden" name="id_detpregunta3" id="udp-id_detpregunta3" value="">
                    <input type="hidden" name="id_detpregunta4" id="udp-id_detpregunta4" value="">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cerrar</button>
                    <button type="submit" class="btn btn-danger">Eliminar</button>
                </form>
            </div>
        </div>
    </div>
</div>