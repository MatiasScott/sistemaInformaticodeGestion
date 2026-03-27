document.addEventListener('DOMContentLoaded', function () {
    const searchInput = document.getElementById('evaluacionSearch');
    const tableRows = document.querySelectorAll('#evaluacionTable tbody tr');

    if (!searchInput) return;

    searchInput.addEventListener('input', function () {
        const searchTerm = this.value.toLowerCase();

        tableRows.forEach(row => {
            const indicador = row.querySelector('.evaluacion-indicador').textContent.toLowerCase();
            const periodo = row.cells[1].textContent.toLowerCase();
            const evaluador = row.cells[8].textContent.toLowerCase();

            if (
                indicador.includes(searchTerm) ||
                periodo.includes(searchTerm) ||
                evaluador.includes(searchTerm)
            ) {
                row.style.display = "";
            } else {
                row.style.display = "none";
            }
        });
    });
});