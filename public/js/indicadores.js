document.addEventListener('DOMContentLoaded', function () {
    const searchInput = document.getElementById('indicadorSearch');
    const tableRows = document.querySelectorAll('#indicadorTable tbody tr');

    if (!searchInput) return;

    searchInput.addEventListener('input', function () {
        const searchTerm = this.value.toLowerCase();

        tableRows.forEach(row => {
            const codigo = row.cells[4].textContent.toLowerCase();
            const nombre = row.querySelector('.indicador-nombre').textContent.toLowerCase();
            const criterio = row.cells[2].textContent.toLowerCase();
            const responsable = row.cells[9].textContent.toLowerCase();

            if (
                codigo.includes(searchTerm) ||
                nombre.includes(searchTerm) ||
                criterio.includes(searchTerm) ||
                responsable.includes(searchTerm)
            ) {
                row.style.display = "";
            } else {
                row.style.display = "none";
            }
        });
    });
});