<?php require BASE_PATH . '/app/views/layout/header.php'; ?>

<div class="card">
    <h2><i class="fas fa-calendar-edit"></i> Editar Periodo: <?= htmlspecialchars($periodo['nombre']) ?></h2>
    <hr class="mb-3">

    <div class="edit-banner">
        <i class="fas fa-exclamation-circle"></i>
        <strong>Nota:</strong> Los cambios en las fechas afectarán la visibilidad de los procesos activos para los usuarios vinculados a este periodo.
    </div>

    <form method="POST" id="formEditPeriodo">
        <h3 class="section-title">Información General</h3>

        <div class="form-group">
            <label for="nombre">Nombre del Periodo:</label>
            <input type="text" name="nombre" id="nombre"
                value="<?= htmlspecialchars($periodo['nombre']) ?>" required style="width: 100%;">
        </div>

        <h3 class="section-title">Cronograma y Estado</h3>

        <div class="date-grid">
            <div class="form-group">
                <label for="fecha_inicio"><i class="far fa-calendar-alt"></i> Fecha Inicio:</label>
                <input type="date" name="fecha_inicio" id="fecha_inicio"
                    value="<?= $periodo['fecha_inicio'] ?>" required>
            </div>

            <div class="form-group">
                <label for="fecha_fin"><i class="far fa-calendar-check"></i> Fecha Fin:</label>
                <input type="date" name="fecha_fin" id="fecha_fin"
                    value="<?= $periodo['fecha_fin'] ?>" required>
            </div>

            <div class="form-group">
                <label for="fecha_limite_documentos"><i class="fas fa-file-upload"></i> Límite Documentos:</label>
                <input type="date" name="fecha_limite_documentos" id="fecha_limite_documentos"
                    value="<?= $periodo['fecha_limite_documentos'] ?>">
            </div>
        </div>

        <div class="form-group" style="width: 32%;">
            <label for="estado">Estado Actual:</label>
            <select name="estado" id="estado" required>
                <option value="activo" <?= $periodo['estado'] == 'activo' ? 'selected' : '' ?>>Activo</option>
                <option value="inactivo" <?= $periodo['estado'] == 'inactivo' ? 'selected' : '' ?>>Inactivo</option>
            </select>
        </div>

        <div class="btn-group mt-4">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-sync-alt"></i> Actualizar Cambios
            </button>
            <a href="<?= URL_PATH ?>periodo" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Volver
            </a>
        </div>
    </form>
</div>

<?php require BASE_PATH . '/app/views/layout/footer.php'; ?>