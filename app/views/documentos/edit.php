<?php
require BASE_PATH . '/app/views/layout/header.php';

$rutaArchivoActual = BASE_PATH . '/public/uploads/' . ($documento['ruta_archivo'] ?? '');
$archivoActualExiste = !empty($documento['ruta_archivo'])
    && is_file($rutaArchivoActual);
?>

<div class="card">

    <h2>
        <i class="fas fa-edit"></i> Editar Documento
    </h2>
    <hr class="mb-3">

    <?php if ($archivoActualExiste): ?>
        <div class="edit-alert">
            <i class="fas fa-info-circle"></i>
            Está modificando una evidencia documental registrada.
            Si no selecciona un nuevo archivo, se conservará el actual.
        </div>
    <?php else: ?>
        <div class="edit-alert" style="border-left-color:#dc3545;">
            <i class="fas fa-exclamation-triangle"></i>
            <strong>El archivo físico de este documento no está disponible.</strong>
            Seleccione nuevamente el archivo para reponerlo sin perder el registro existente.
        </div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data">

        <!-- ===================== -->
        <h3 class="section-title">Clasificación</h3>

        <div class="d-flex mb-4">
            <div class="flex-1 form-group">
                <label for="periodo_id">Periodo:</label>
                <select name="periodo_id" id="periodo_id" required>
                    <?php $periodos = $periodos ?? []; ?>
                    <?php foreach ($periodos as $p): ?>
                        <option value="<?= $p['id'] ?>"
                            <?= $p['id'] == $documento['periodo_id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($p['nombre']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="flex-1 form-group">
                <label for="indicador_id">Indicador:</label>
                <select name="indicador_id" id="indicador_id" required>
                    <?php $indicadores = $indicadores ?? []; ?>
                    <?php foreach ($indicadores as $i): ?>
                        <option value="<?= $i['id'] ?>"
                            <?= $i['id'] == $documento['indicador_id'] ? 'selected' : '' ?>>
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
                    <option value="<?= $ev['id'] ?>"
                        <?= $ev['id'] == $documento['evaluacion_id'] ? 'selected' : '' ?>>
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
                <input type="text"
                    name="proceso"
                    id="proceso"
                    value="<?= htmlspecialchars($documento['proceso'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
            </div>

            <div class="flex-1 form-group">
                <label for="subproceso">Subproceso:</label>
                <input type="text"
                    name="subproceso"
                    id="subproceso"
                    value="<?= htmlspecialchars($documento['subproceso'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
            </div>
        </div>

        <div class="form-group mb-4">
            <label for="codigo">Código:</label>
            <input type="text"
                name="codigo"
                id="codigo"
                value="<?= htmlspecialchars($documento['codigo'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
        </div>

        <!-- ===================== -->
        <h3 class="section-title">Archivo</h3>

        <div class="form-group mb-3">
            <label>Archivo Actual:</label>

            <?php if ($archivoActualExiste): ?>
                <div class="file-current">
                    <i class="fas fa-file-alt"></i>
                    <?= htmlspecialchars($documento['nombre_archivo'] ?? '', ENT_QUOTES, 'UTF-8') ?>
                </div>
            <?php else: ?>
                <div class="file-current" style="border-color:#dc3545;">
                    <i class="fas fa-exclamation-triangle"></i>
                    <strong>Archivo no disponible en el servidor</strong>
                    <?php if (!empty($documento['nombre_archivo'])): ?>
                        — <?= htmlspecialchars($documento['nombre_archivo'], ENT_QUOTES, 'UTF-8') ?>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>

        <div class="form-group mb-4">
            <label for="archivo">
                <?= $archivoActualExiste ? 'Cambiar Archivo (Opcional):' : 'Reponer Archivo (Obligatorio):' ?>
            </label>

            <input type="file"
                name="archivo"
                id="archivo"
                <?= $archivoActualExiste ? '' : 'required' ?>>

            <small id="fileNamePreview" class="text-muted mt-2">
                <?php if ($archivoActualExiste): ?>
                    Tamaño máximo permitido: 8 MB.
                <?php else: ?>
                    Debe seleccionar el archivo correspondiente. Tamaño máximo permitido: 8 MB.
                <?php endif; ?>
            </small>
        </div>

        <!-- ===================== -->
        <h3 class="section-title">Estado y Observaciones</h3>

        <div class="form-group mb-4">
            <label for="estado">Estado:</label>
            <select name="estado" id="estado">
                <option value="pendiente"
                    <?= $documento['estado'] == 'pendiente' ? 'selected' : '' ?>>
                    Pendiente
                </option>
                <option value="aprobado"
                    <?= $documento['estado'] == 'aprobado' ? 'selected' : '' ?>>
                    Aprobado
                </option>
                <option value="rechazado"
                    <?= $documento['estado'] == 'rechazado' ? 'selected' : '' ?>>
                    Rechazado
                </option>
            </select>
        </div>

        <div class="form-group mb-4">
            <label for="observaciones">Observaciones:</label>
            <textarea name="observaciones"
                id="observaciones"
                rows="3"><?= htmlspecialchars($documento['observaciones'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
        </div>

        <!-- ===================== -->
        <div class="btn-group mt-3">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> Actualizar Documento
            </button>

            <a href="<?= URL_PATH ?>documentos" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Volver
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
    const archivoActualExiste = <?= $archivoActualExiste ? 'true' : 'false' ?>;
    const mensajeSinArchivo = archivoActualExiste
        ? 'Tamaño máximo permitido: 8 MB.'
        : 'Debe seleccionar el archivo correspondiente. Tamaño máximo permitido: 8 MB.';

    if (!form || !fileInput || !submitButton) {
        return;
    }

    fileInput.addEventListener('change', function () {
        const file = this.files[0];

        if (!file) {
            preview.textContent = mensajeSinArchivo;
            return;
        }

        if (file.size > maxSize) {
            alert('El archivo supera el tamaño máximo permitido de 8 MB.');
            this.value = '';
            preview.textContent = mensajeSinArchivo;
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
            '<i class="fas fa-spinner fa-spin"></i> Guardando, por favor espere...';
    });
});
</script>

<?php require BASE_PATH . '/app/views/layout/footer.php'; ?>