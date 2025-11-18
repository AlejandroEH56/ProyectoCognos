window.addEventListener('DOMContentLoaded', function (event) {
    // Simple-DataTables
    // https://github.com/fiduswriter/Simple-DataTables/wiki
    const datatablesSimple = document.getElementById('datatablesSimple');
    if (datatablesSimple) {
        new simpleDatatables.DataTable(datatablesSimple);
    }
});

// Listener para activar el modal
document.addEventListener('DOMContentLoaded', function () {
    const editModal = document.getElementById('editModal');
    const deleteModal = document.getElementById('deleteModal');

    editModal.addEventListener('show.bs.modal', function (event) {
        const button = event.relatedTarget;
        // Se obtienen los datos del boton
        const id = button.getAttribute('data-id');
        const titulo = button.getAttribute('data-titulo');
        const contenido = button.getAttribute('data-contenido');
        // Actualiza el contenido del modal
        document.getElementById('editPostId').value = id;
        document.getElementById('editTitulo').value = titulo;
        document.getElementById('editContenido').value = contenido;
    });

    deleteModal.addEventListener('show.bs.modal', function (event) {
        const button = event.relatedTarget;
        // Se obtienen los datos del boton
        const id = button.getAttribute('data-id');
        // Actualiza el contenido del modal
        document.getElementById('deletePostId').value = id;
    });
});

