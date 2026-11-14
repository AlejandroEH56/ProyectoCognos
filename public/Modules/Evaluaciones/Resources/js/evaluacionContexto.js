// Presentación compartida por los formularios del módulo de evaluaciones.
document.addEventListener('DOMContentLoaded', () => {
    const persona = document.getElementById('evaluacion-persona');
    const perfil = document.getElementById('evaluacion-perfil');
    if (!persona || !perfil) return;
    const mostrarPerfil = () => {
        perfil.value = persona.selectedOptions[0]?.dataset.perfil || 'Selecciona una persona';
    };
    persona.addEventListener('change', mostrarPerfil);
    persona.form.addEventListener('reset', () => queueMicrotask(mostrarPerfil));
    mostrarPerfil();
});
