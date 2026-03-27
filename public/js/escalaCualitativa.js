document.addEventListener('DOMContentLoaded', function () {
    const searchInput = document.getElementById('escalaSearch');
    if (!searchInput) return;
    const tableRows = document.querySelectorAll('#escalaTable tbody tr');

    searchInput.addEventListener('input', function () {
        const searchTerm = this.value.toLowerCase();

        tableRows.forEach(row => {
            const nombre = row.querySelector('.escala-nombre').textContent.toLowerCase();
            const valor = row.cells[1].textContent.toLowerCase();

            if (nombre.includes(searchTerm) || valor.includes(searchTerm)) {
                row.style.display = "";
            } else {
                row.style.display = "none";
            }
        });
    });
});