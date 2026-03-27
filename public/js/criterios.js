document.addEventListener('DOMContentLoaded', function () {
    const searchInput = document.getElementById('criterioSearch');
    const tableRows = document.querySelectorAll('#criterioTable tbody tr');

    if (!searchInput) return;

    searchInput.addEventListener('input', function () {
        const searchTerm = this.value.toLowerCase();

        tableRows.forEach(row => {
            const nombre = row.querySelector('.criterio-nombre').textContent.toLowerCase();
            const periodo = row.cells[1].textContent.toLowerCase();
            const peso = row.cells[3].textContent.toLowerCase();

            if (nombre.includes(searchTerm) ||
                periodo.includes(searchTerm) ||
                peso.includes(searchTerm)) {
                row.style.display = "";
            } else {
                row.style.display = "none";
            }
        });
    });
});