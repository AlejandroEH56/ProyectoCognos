// Listener para activar modales formulario kostick (editar)
document.addEventListener('DOMContentLoaded', function () {
    const deletePregunta = document.getElementById('deleteModalPregunta');

    deletePregunta.addEventListener('show.bs.modal', function (event) {
        const button = event.relatedTarget;
        const pregunta = JSON.parse(button.getAttribute('data-pregunta'));
        // Llena los campos del formulario con los datos de la empresa
        document.getElementById('udp-id_pregunta').value = pregunta.id_pregunta || '';
        document.getElementById('udp-id_detpregunta1').value = pregunta.opciones[0].idRes || '';
        document.getElementById('udp-id_detpregunta2').value = pregunta.opciones[1].idRes || '';
        document.getElementById('udp-id_detpregunta3').value = pregunta.opciones[2].idRes || '';
        document.getElementById('udp-id_detpregunta4').value = pregunta.opciones[3].idRes || '';

        // Mensaje personalizado
        document.getElementById("body-msg").innerHTML =
            `<p>¿Estás seguro de que deseas eliminar esta pregunta y sus respuestas?</p>
                <p><strong>${pregunta.pregunta}</strong></p>
                    <ol>
                    <li>${pregunta.opciones[0].campo}</li>
                    <li>${pregunta.opciones[1].campo}</li>
                    <li>${pregunta.opciones[2].campo}</li>
                    <li>${pregunta.opciones[3].campo}</li>
                </ol>`;
    });
});