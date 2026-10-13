<?php require BASE_PATH . '/app/views/layout/header.php'; ?>

<div class="card">

    <h2>
        <i class="fas fa-upload"></i> Subir Evidencia Documental
    </h2>
    <hr class="mb-3">

    <div class="scale-info">
        <i class="fas fa-info-circle"></i>
        Cargue documentos que respalden el cumplimiento del indicador.
        Asegúrese de que el archivo corresponda al periodo seleccionado.
    </div>

    <form method="POST" action="<?= URL_PATH ?>documentos/subir" enctype="multipart/form-data">

        <!-- ===================== -->
        <h3 class="section-title">Clasificación</h3>

        <div class="d-flex mb-4">
            <div class="flex-1 form-group">
                <label for="periodo_id">Periodo:</label>
                <select name="periodo_id" id="periodo_id" required>
                    <?php $periodos = $periodos ?? []; ?>
                    <?php foreach ($periodos as $p): ?>
                        <option value="<?= $p['id'] ?>">
                            <?= htmlspecialchars($p['nombre']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="flex-1 form-group">
                <label for="indicador_id">Indicador (Opcional):</label>
                <select name="indicador_id" id="indicador_id">
                    <option value="">Seleccionar</option>
                    <?php $indicadores = $indicadores ?? []; ?>
                    <?php foreach ($indicadores as $i): ?>
                        <option value="<?= $i['id'] ?>">
                            <?= htmlspecialchars($i['nombre']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="form-group mb-4">
            <label for="evaluacion_id">Evaluación (Opcional):</label>
            <select name="evaluacion_id" id="evaluacion_id">
                <option value="">Seleccionar</option>
                <?php $evaluaciones = $evaluaciones ?? []; ?>
                <?php foreach ($evaluaciones as $ev): ?>
                    <option value="<?= $ev['id'] ?>">
                        Evaluación #<?= $ev['id'] ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- ===================== -->
        <h3 class="section-title">Información del Documento</h3>

        <div class="d-flex mb-4">
            <div class="flex-1 form-group">
                <label for="proceso">Proceso:</label>
                <input type="text" name="proceso" id="proceso" required>
            </div>

            <div class="flex-1 form-group">
                <label for="subproceso">Subproceso:</label>
                <input type="text" name="subproceso" id="subproceso" required>
            </div>
        </div>

        <div class="form-group mb-4">
            <label for="codigo">Código:</label>
            <input type="text" name="codigo" id="codigo" required>
        </div>

        <!-- ===================== -->
        <h3 class="section-title">Archivo</h3>

        <div class="form-group mb-4">
            <label for="nombre_archivo">Nombre del Documento:</label>
            <input type="text"
                name="nombre_archivo"
                id="nombre_archivo"
                required
                placeholder="Ej: Informe de Autoevaluación 2025">
        </div>

        <div class="form-group mb-4">
            <label for="archivo">Seleccionar Archivo:</label>
            <input type="file"
                name="archivo"
                id="archivo"
                required>

            <small id="fileNamePreview" class="text-muted mt-2">
                Tamaño máximo permitido: 8 MB.
            </small>
        </div>

        <!-- ===================== -->
        <h3 class="section-title">Estado y Observaciones</h3>

        <div class="form-group mb-4">
            <label for="estado">Estado:</label>
            <select name="estado" id="estado">
                <option value="pendiente">Pendiente</option>
                <option value="aprobado">Aprobado</option>
                <option value="rechazado">Rechazado</option>
            </select>
        </div>

        <div class="form-group mb-4">
            <label for="observaciones">Observaciones:</label>
            <textarea name="observaciones"
                id="observaciones"
                rows="3"></textarea>
        </div>

        <!-- ===================== -->
        <div class="btn-group mt-3">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-cloud-upload-alt"></i> Subir Documento
            </button>

            <a href="<?= URL_PATH ?>documentos" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Cancelar
            </a>
        </div>

    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.querySelector('form[enctype="multipart/form-data"]');
    const fileInput = document.getElementById('archivo');
    const preview = document.getElementById('fileNamePreview');
    const submitButton = form ? form.querySelector('button[type="submit"]') : null;
    const maxSize = 8 * 1024 * 1024;

    if (!form || !fileInput || !submitButton) {
        return;
    }

    fileInput.addEventListener('change', function () {
        const file = this.files[0];

        if (!file) {
            preview.textContent = 'Tamaño máximo permitido: 8 MB.';
            return;
        }

        if (file.size > maxSize) {
            alert('El archivo supera el tamaño máximo permitido de 8 MB.');
            this.value = '';
            preview.textContent = 'Tamaño máximo permitido: 8 MB.';
            return;
        }

        preview.textContent =
            file.name + ' (' + (file.size / 1024 / 1024).toFixed(2) + ' MB)';
    });

    form.addEventListener('submit', function (event) {
        const file = fileInput.files[0];

        if (file && file.size > maxSize) {
            event.preventDefault();
            alert('El archivo supera el tamaño máximo permitido de 8 MB.');
            return;
        }

        submitButton.disabled = true;
        submitButton.innerHTML =
            '<i class="fas fa-spinner fa-spin"></i> Subiendo documento, por favor espere...';
    });
});
</script>

<?php require BASE_PATH . '/app/views/layout/footer.php'; ?>