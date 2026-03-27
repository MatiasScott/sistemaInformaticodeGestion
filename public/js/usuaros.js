document.addEventListener('DOMContentLoaded', function () {
    const searchInput = document.getElementById('userSearch');
    if (!searchInput) return;
    const tableRows = document.querySelectorAll('tbody tr');

    searchInput.addEventListener('input', function () {
        const searchTerm = this.value.toLowerCase();

        tableRows.forEach(row => {
            // Obtenemos el texto de las columnas relevantes
            const nombre = row.cells[1].textContent.toLowerCase();
            const email = row.cells[2].textContent.toLowerCase();
            const roles = row.cells[3].textContent.toLowerCase();
            const cargos = row.cells[4].textContent.toLowerCase();

            // Verificamos si el término de búsqueda está en alguna de ellas
            if (nombre.includes(searchTerm) ||
                email.includes(searchTerm) ||
                roles.includes(searchTerm) ||
                cargos.includes(searchTerm)) {
                row.style.display = ""; // Muestra la fila
            } else {
                row.style.display = "none"; // Oculta la fila
            }
        });
    });
});