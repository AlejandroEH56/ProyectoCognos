
// Funcion principal para validar los radios de cada pregunta
function validarRadiosGrupo(clase, errorId) {
    const nombres = [];
    const valores = [];
    // Obtener todos los grupos (por name)
    document.querySelectorAll('.' + clase).forEach(input => {
        if (!nombres.includes(input.name)) {
            nombres.push(input.name);
        }
    });
    // Obtener el valor seleccionado de cada grupo
    nombres.forEach(name => {
        const seleccionado = document.querySelector('input[name="' + name + '"]:checked');
        if (seleccionado) {
            valores.push(seleccionado.value);
        }
    });
    // Validar que no se repitan valores
    const set = new Set(valores);
    if (valores.length !== set.size) {
        document.getElementById(errorId).textContent = "Los valores no se pueden repetir en el grupo.";
        return false;
    } else {
        document.getElementById(errorId).textContent = "";
        return true;
    }
}

// Función helper para adjuntar eventos 'change' a un grupo (se mantiene igual)
function adjuntarListenersChange(grupo) {
    const errorId = grupo + 'Error'; // Patrón dinámico: 'grupo1' → 'grupo1Error'
    document.querySelectorAll('.' + grupo).forEach(radio => {
        radio.addEventListener('change', () => validarRadiosGrupo(grupo, errorId));
    });
}

// Función para detectar grupos automáticamente (Todo se hace de manera dinamica)
function detectarGrupos() {
    const grupos = new Set(); // Set para únicos
    // Se buscan todos los radios en el formulario (ID del formulario)
    document.querySelectorAll('#evaluacionTestZabic input[type="radio"]').forEach(input => {
        // Obtener clases del input y filtrar las que match 'grupoX' (X = número)
        const clases = input.className.split(' '); // Split por espacios
        clases.forEach(clase => {
            if (clase.match(/^grupo\d+$/)) { // Regex: 'grupo' seguido de dígitos (e.g., grupo1, ..., grupo10)
                grupos.add(clase);
            }
        });
    });
    // Convertir a array y ordenar por número (e.g., ['grupo1', 'grupo2', 'grupo10'])
    return Array.from(grupos).sort((a, b) => {
        const numA = parseInt(a.replace('grupo', ''));
        const numB = parseInt(b.replace('grupo', ''));
        return numA - numB;
    });
}

// Inicialización dinámica (ejecutar al cargar DOM)
document.addEventListener('DOMContentLoaded', function () {
    const grupos = detectarGrupos(); // Detecta automáticamente: ['grupo1', 'grupo2', ..., 'grupoN']

    if (grupos.length === 0) {
        console.warn('No se detectaron grupos de radios en el formulario.');
        return;
    }

    console.log('Grupos detectados:', grupos);

    // Adjuntar listeners 'change' para TODOS los grupos detectados (dinámico)
    grupos.forEach(grupo => {
        adjuntarListenersChange(grupo);
    });

    // Manejador de submit optimizado (validación dinámica para TODOS los grupos)
    document.getElementById('evaluacionTestZabic').addEventListener('submit', function (e) {
        let esValido = true; // Booleano general

        grupos.forEach(grupo => {
            const errorId = grupo + 'Error';
            const grupoValido = validarRadiosGrupo(grupo, errorId);
            if (!grupoValido) {
                esValido = false; // Cualquiera inválido → Bloquea submit
            }
        });

        if (!esValido) {
            e.preventDefault(); // Previene envío
            // Opcional: Mensaje general para UX
            alert('Por favor, corrige los errores en los grupos antes de enviar.');
        }
    });
});