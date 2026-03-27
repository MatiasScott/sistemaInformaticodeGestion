<?php require BASE_PATH . '/app/views/layout/header.php'; ?>

<div class="card">
    <h2><i class="fas fa-calendar-plus"></i> Crear Nuevo Periodo</h2>
    <hr class="mb-3">

    <div class="info-periodo">
        <i class="fas fa-info-circle"></i>
        Defina el rango de tiempo para este periodo. Los periodos activos permiten a los usuarios realizar procesos dentro de las fechas establecidas.
    </div>

    <form method="POST" id="formPeriodo">
        <h3 class="section-title">Información General</h3>

        <div class="form-group">
            <label for="nombre">Nombre del Periodo:</label>
            <input type="text" name="nombre" id="nombre" placeholder="Ej: Primer Semestre 2024, Evaluación Anual..." required style="width: 100%;">
        </div>

        <h3 class="section-title">Cronograma y Estado</h3>

        <div class="date-grid">
            <div class="form-group">
                <label for="fecha_inicio"><i class="far fa-calendar-alt"></i> Fecha Inicio:</label>
                <input type="date" name="fecha_inicio" id="fecha_inicio" required>
            </div>

            <div class="form-group">
                <label for="fecha_fin"><i class="far fa-calendar-check"></i> Fecha Fin:</label>
                <input type="date" name="fecha_fin" id="fecha_fin" required>
            </div>

            <div class="form-group">
                <label for="fecha_limite_documentos"><i class="fas fa-file-upload"></i> Límite Documentos:</label>
                <input type="date" name="fecha_limite_documentos" id="fecha_limite_documentos">
            </div>
        </div>

        <div class="form-group" style="width: 32%;">
            <label for="estado">Estado del Periodo:</label>
            <select name="estado" id="estado" required>
                <option value="activo" selected>Activo</option>
                <option value="inactivo">Inactivo</option>
            </select>
        </div>

        <div class="btn-group mt-4">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> Guardar Periodo
            </button>
            <a href="<?= URL_PATH ?>periodo" class="btn btn-secondary">
                <i class="fas fa-times"></i> Cancelar
            </a>
        </div>
    </form>
</div>

<?php require BASE_PATH . '/app/views/layout/footer.php'; ?>