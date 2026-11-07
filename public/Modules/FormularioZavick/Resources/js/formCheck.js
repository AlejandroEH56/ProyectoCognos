function validarRadiosGrupo(clase, errorId) {
    const formulario = document.getElementById('evaluacionTestZabic');
    const radios = Array.from(formulario.querySelectorAll('input.' + clase));
    const nombres = new Set(radios.map(radio => radio.name));
    const valores = radios.filter(radio => radio.checked).map(radio => radio.value);
    const completos = nombres.size === 4 && valores.length === 4;
    const diferentes = new Set(valores).size === valores.length;
    const error = document.getElementById(errorId);
    if (error) {
        error.textContent = diferentes ? '' : 'Los valores no se pueden repetir en el grupo.';
        if (!completos && diferentes) {
            error.textContent = 'Asigna un valor a cada una de las cuatro opciones.';
        }
    }
    return completos && diferentes;
}

function adjuntarListenersChange(grupo) {
    document.querySelectorAll('#evaluacionTestZabic input.' + grupo).forEach(radio => {
        radio.addEventListener('change', () => validarRadiosGrupo(grupo, grupo + 'Error'));
    });
}

function detectarGrupos() {
    const grupos = new Set();
    document.querySelectorAll('#evaluacionTestZabic input[type="radio"]').forEach(radio => {
        radio.classList.forEach(clase => {
            if (/^grupo\d+$/.test(clase)) grupos.add(clase);
        });
    });
    return Array.from(grupos).sort((a, b) => Number(a.slice(5)) - Number(b.slice(5)));
}

document.addEventListener('DOMContentLoaded', () => {
    const formulario = document.getElementById('evaluacionTestZabic');
    if (!formulario) return;
    const grupos = detectarGrupos();
    grupos.forEach(adjuntarListenersChange);
    formulario.addEventListener('submit', event => {
        const resultados = grupos.map(grupo => validarRadiosGrupo(grupo, grupo + 'Error'));
        if (grupos.length === 0 || resultados.includes(false)) event.preventDefault();
    });
    formulario.addEventListener('reset', () => {
        grupos.forEach(grupo => {
            const error = document.getElementById(grupo + 'Error');
            if (error) error.textContent = '';
        });
    });
});
