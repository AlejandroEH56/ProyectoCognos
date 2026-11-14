window.addEventListener('DOMContentLoaded', function (event) {
    // Simple-DataTables
    // https://github.com/fiduswriter/Simple-DataTables/wiki
    const datatablesSimple = document.getElementById('datatablesSimple');
    if (datatablesSimple && typeof simpleDatatables !== 'undefined') {
        new simpleDatatables.DataTable(datatablesSimple);
    }
});

// Listener para activar modales usuario
document.addEventListener('DOMContentLoaded', function () {
    const editModalUser = document.getElementById('editModalUser');
    const deleteModalUser = document.getElementById('deleteModalUser');

    editModalUser.addEventListener('show.bs.modal', function (event) {
        const button = event.relatedTarget;
        // Se obtienen los datos del boton
        const usuario = JSON.parse(button.getAttribute('data-user'));

        // Llena los campos del formulario con los datos de la empresa
        document.getElementById('usr-Id').value = usuario.ID || '';
        document.getElementById('usr-nombre').value = usuario.nombre || '';
        document.getElementById('usr-apellidos').value = usuario.apellido || '';
        document.getElementById('usr-curp').value = usuario.curp || '';
        document.getElementById('usr-rfc').value = usuario.rfc || '';
        document.getElementById('usr-nivel').value = usuario.nivel_academico || '';
        document.getElementById('usr-perfil').value = usuario.id_perfil || '';
    });

    deleteModalUser.addEventListener('show.bs.modal', function (event) {
        const button = event.relatedTarget;
        // Se obtienen los datos del boton
        const id = button.getAttribute('data-user-id');
        const name = button.getAttribute('data-user-name');
        // Actualiza el contenido del modal
        document.getElementById('deleteUserId').value = id;
        document.getElementById('body-msg').textContent = `¿Estás seguro de que deseas eliminar esta persona (${name})?`;
    });

});

