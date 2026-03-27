document.addEventListener('DOMContentLoaded', function () {
    const searchInput = document.getElementById('permSearch');
    if (!searchInput) return;
    const tableRows = document.querySelectorAll('#permisosTable tbody tr');

    searchInput.addEventListener('input', function () {
        const searchTerm = this.value.toLowerCase();

        tableRows.forEach(row => {
            // Buscamos en las celdas de Módulo (1) y Acción (2)
            const modulo = row.cells[1].textContent.toLowerCase();
            const accion = row.cells[2].textContent.toLowerCase();

            if (modulo.includes(searchTerm) || accion.includes(searchTerm)) {
                row.style.display = "";
            } else {
                row.style.display = "none";
            }
        });
    });
});