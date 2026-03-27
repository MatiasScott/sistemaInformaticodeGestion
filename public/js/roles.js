document.addEventListener('DOMContentLoaded', function () {
    const searchInput = document.getElementById('roleSearch');
    if (!searchInput) return;
    const tableRows = document.querySelectorAll('#rolesTable tbody tr');

    searchInput.addEventListener('input', function () {
        const searchTerm = this.value.toLowerCase();

        tableRows.forEach(row => {
            const nombre = row.querySelector('.role-name').textContent.toLowerCase();

            if (nombre.includes(searchTerm)) {
                row.style.display = "";
            } else {
                row.style.display = "none";
            }
        });
    });
});

document.addEventListener('DOMContentLoaded', function () {
    // Escuchar cambios en los checkboxes de "Marcar todo"
    const masterCheckboxes = document.querySelectorAll('.select-all-module');

    masterCheckboxes.forEach(master => {
        master.addEventListener('change', function () {
            const modulo = this.getAttribute('data-modulo');
            const childCheckboxes = document.querySelectorAll('.perm-check-' + modulo);

            childCheckboxes.forEach(child => {
                child.checked = this.checked;
            });
        });
    });

    // Lógica inversa: si desmarcas uno manual, desmarcar el "Marcar todo"
    const allChecks = document.querySelectorAll('input[name="permisos[]"]');
    allChecks.forEach(check => {
        check.addEventListener('change', function () {
            const moduloClass = this.className; // Ej: perm-check-usuarios
            const moduloName = moduloClass.replace('perm-check-', '');
            const master = document.querySelector(`.select-all-module[data-modulo="${moduloName}"]`);

            const totalInModule = document.querySelectorAll('.' + moduloClass).length;
            const checkedInModule = document.querySelectorAll('.' + moduloClass + ':checked').length;

            master.checked = (totalInModule === checkedInModule);
        });
    });
});