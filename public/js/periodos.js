document.addEventListener('DOMContentLoaded', function () {
    const searchInput = document.getElementById('periodoSearch');
    if (!searchInput) return;
    const tableRows = document.querySelectorAll('#periodosTable tbody tr');

    searchInput.addEventListener('input', function () {
        const searchTerm = this.value.toLowerCase();

        tableRows.forEach(row => {
            const nombre = row.querySelector('.periodo-name').textContent.toLowerCase();
            if (nombre.includes(searchTerm)) {
                row.style.display = "";
            } else {
                row.style.display = "none";
            }
        });
    });
});

document.addEventListener('DOMContentLoaded', function () {
    const startInput = document.getElementById('fecha_inicio');
    const endInput = document.getElementById('fecha_fin');
    const docInput = document.getElementById('fecha_limite_documentos');
    const formPeriodo = document.getElementById('formPeriodo');

    if (!startInput || !endInput || !formPeriodo) return;

    // Validación: La fecha de fin no puede ser menor a la de inicio
    startInput.addEventListener('change', function () {
        if (this.value) {
            endInput.min = this.value;
            if (docInput) docInput.min = this.value;
        }
    });

    // Validación al enviar
    formPeriodo.addEventListener('submit', function (e) {
        if (endInput.value < startInput.value) {
            e.preventDefault();
            alert('La fecha de fin no puede ser anterior a la fecha de inicio.');
        }
    });
});

document.addEventListener('DOMContentLoaded', function () {
    const startInput = document.getElementById('fecha_inicio');
    const endInput = document.getElementById('fecha_fin');
    const formEditPeriodo = document.getElementById('formEditPeriodo');

    if (!startInput || !endInput || !formEditPeriodo) return;

    // Al cargar, establecer el mínimo de la fecha fin basado en el valor actual de inicio
    if (startInput.value) {
        endInput.min = startInput.value;
    }

    // Actualizar dinámicamente el mínimo si cambia la fecha de inicio
    startInput.addEventListener('change', function () {
        endInput.min = this.value;
    });

    // Validación extra antes de enviar
    formEditPeriodo.addEventListener('submit', function (e) {
        if (endInput.value && startInput.value && endInput.value < startInput.value) {
            e.preventDefault();
            alert('Error: La fecha de finalización no puede ser anterior a la de inicio.');
        }
    });
});