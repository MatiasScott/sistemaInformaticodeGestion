document.addEventListener('DOMContentLoaded', function () {
    const searchInput = document.getElementById('subcriterioSearch');
    const tableRows = document.querySelectorAll('#subcriterioTable tbody tr');

    if (!searchInput) return;

    searchInput.addEventListener('input', function () {
        const searchTerm = this.value.toLowerCase();

        tableRows.forEach(row => {
            const nombre = row.querySelector('.subcriterio-nombre').textContent.toLowerCase();
            const criterio = row.cells[1].textContent.toLowerCase();
            const peso = row.cells[3].textContent.toLowerCase();

            if (nombre.includes(searchTerm) ||
                criterio.includes(searchTerm) ||
                peso.includes(searchTerm)) {
                row.style.display = "";
            } else {
                row.style.display = "none";
            }
        });
    });
});