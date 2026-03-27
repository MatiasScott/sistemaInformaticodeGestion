<?php require BASE_PATH . '/app/views/layout/header.php'; ?>

<div class="card">

    <h2>
        <i class="fas fa-edit"></i> Editar Documento
    </h2>
    <hr class="mb-3">

    <div class="edit-alert">
        <i class="fas fa-info-circle"></i>
        Está modificando una evidencia documental registrada.
        Si no selecciona un nuevo archivo, se conservará el actual.
    </div>

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
                    value="<?= htmlspecialchars($documento['proceso']) ?>">
            </div>

            <div class="flex-1 form-group">
                <label for="subproceso">Subproceso:</label>
                <input type="text"
                    name="subproceso"
                    id="subproceso"
                    value="<?= htmlspecialchars($documento['subproceso']) ?>">
            </div>
        </div>

        <div class="form-group mb-4">
            <label for="codigo">Código:</label>
            <input type="text"
                name="codigo"
                id="codigo"
                value="<?= htmlspecialchars($documento['codigo']) ?>">
        </div>

        <!-- ===================== -->
        <h3 class="section-title">Archivo</h3>

        <div class="form-group mb-3">
            <label>Archivo Actual:</label>
            <div class="file-current">
                <i class="fas fa-file-alt"></i>
                <?= htmlspecialchars($documento['nombre_archivo']) ?>
            </div>
        </div>

        <div class="form-group mb-4">
            <label for="archivo">Cambiar Archivo (Opcional):</label>
            <input type="file" name="archivo" id="archivo">
            <small id="fileNamePreview" class="text-muted mt-2"></small>
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
                rows="3"><?= htmlspecialchars($documento['observaciones']) ?></textarea>
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

<?php require BASE_PATH . '/app/views/layout/footer.php'; ?>