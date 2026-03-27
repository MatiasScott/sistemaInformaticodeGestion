document.addEventListener('DOMContentLoaded', function () {

    const searchInput = document.getElementById('documentoSearch');
    const rows = document.querySelectorAll('#documentoTable tbody tr');

    if (!searchInput) return;

    searchInput.addEventListener('input', function () {
        const searchTerm = this.value.toLowerCase();

        rows.forEach(row => {
            const indicador = row.querySelector('.doc-indicador').textContent.toLowerCase();
            const periodo = row.cells[1].textContent.toLowerCase();
            const archivo = row.cells[6].textContent.toLowerCase();

            if (
                indicador.includes(searchTerm) ||
                periodo.includes(searchTerm) ||
                archivo.includes(searchTerm)
            ) {
                row.style.display = "";
            } else {
                row.style.display = "none";
            }
        });
    });

});

document.addEventListener('DOMContentLoaded', function () {

    const fileInput = document.getElementById('archivo');
    const preview = document.getElementById('fileNamePreview');

    if (!fileInput) return;

    fileInput.addEventListener('change', function () {
        if (this.files.length > 0) {
            preview.textContent = "Archivo seleccionado: " + this.files[0].name;
        } else {
            preview.textContent = "";
        }
    });

});

document.addEventListener('DOMContentLoaded', function () {

    const fileInput = document.getElementById('archivo');
    const preview = document.getElementById('fileNamePreview');

    if (!fileInput) return;

    fileInput.addEventListener('change', function () {
        if (this.files.length > 0) {
            preview.textContent = "Nuevo archivo seleccionado: " + this.files[0].name;
        } else {
            preview.textContent = "";
        }
    });

});