document.addEventListener('DOMContentLoaded', function () {
    const searchInput = document.getElementById('cargoSearch');
    if (!searchInput) return;
    const tableRows = document.querySelectorAll('#cargosTable tbody tr');

    searchInput.addEventListener('input', function () {
        const searchTerm = this.value.toLowerCase();

        tableRows.forEach(row => {
            const nombre = row.querySelector('.cargo-name').textContent.toLowerCase();

            if (nombre.includes(searchTerm)) {
                row.style.display = "";
            } else {
                row.style.display = "none";
            }
        });
    });
});