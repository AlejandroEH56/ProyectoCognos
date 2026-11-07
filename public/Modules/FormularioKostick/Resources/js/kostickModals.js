// Listener para activar modales formulario kostick (editar)
document.addEventListener('DOMContentLoaded', function () {
    const deletePregunta = document.getElementById('deleteModalPregunta');

    deletePregunta.addEventListener('show.bs.modal', function (event) {
        const button = event.relatedTarget;

        // Se obtienen los datos del boton
        const id1 = button.getAttribute('data-preg-id1');
        const id2 = button.getAttribute('data-preg-id2');
        const preg1 = button.getAttribute('data-preg-dat1');
        const preg2 = button.getAttribute('data-preg-dat2');
        // Actualiza el contenido del modal
        document.getElementById('id_detpregunta1').value = id1;
        document.getElementById('id_detpregunta2').value = id2;
        document.getElementById
        document.getElementById("body-msg").innerHTML = `<p>¿Estás seguro de que deseas eliminar esta pregunta (<strong>${preg1}/${preg2}</strong>)?</p>`;
    });
});