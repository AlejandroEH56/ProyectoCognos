document.addEventListener('DOMContentLoaded', () => {
    const modal = document.getElementById('deleteModalPregunta');
    if (!modal) return;
    modal.addEventListener('show.bs.modal', event => {
        const pregunta = JSON.parse(event.relatedTarget.getAttribute('data-pregunta'));
        document.getElementById('udp-id_pregunta').value = pregunta.id_pregunta;
        for (let i = 0; i < 4; i++) {
            document.getElementById('udp-id_detpregunta' + (i + 1)).value = pregunta.opciones[i]?.idRes ?? '';
        }
        const mensaje = document.getElementById('body-msg');
        const descripcion = document.createElement('p');
        descripcion.textContent = pregunta.pregunta;
        const lista = document.createElement('ol');
        pregunta.opciones.forEach(opcion => {
            const elemento = document.createElement('li');
            elemento.textContent = opcion.campo;
            lista.appendChild(elemento);
        });
        mensaje.replaceChildren('¿Estás seguro de que deseas eliminar esta pregunta y sus respuestas?', descripcion, lista);
    });
});
