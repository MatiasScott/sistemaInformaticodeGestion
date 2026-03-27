<?php require BASE_PATH . '/app/views/layout/header.php'; ?>

<div class="card">
    <h2><i class="fas fa-edit"></i> Editar Escala: <?= htmlspecialchars($data['nombre'] ?? 'Cualitativa') ?></h2>
    <hr class="mb-3">

    <div class="edit-alert">
        <i class="fas fa-exclamation-triangle"></i>
        <strong>Atención:</strong> Los cambios en el valor numérico afectarán los cálculos de todos los indicadores que utilicen esta escala actualmente.
    </div>

    <form method="POST">
        <h3 class="section-title">Actualizar Parámetros</h3>

        <div class="form-group mb-4">
            <label for="nombre">Nombre de la Escala:</label>
            <input
                type="text"
                id="nombre"
                name="nombre"
                value="<?= htmlspecialchars($data['nombre'] ?? '') ?>"
                required
                style="width: 100%;">
            <small class="text-muted">Nombre que identifica el rango de calificación.</small>
        </div>

        <div class="d-flex mb-4">
            <div class="flex-1 form-group">
                <label for="valor">Valor de la Escala:</label>
                <input
                    type="number"
                    id="valor"
                    name="valor"
                    value="<?= htmlspecialchars($data['valor'] ?? '') ?>"
                    step="0.01"
                    required>
                <small class="text-muted">Valor decimal o entero para el cálculo.</small>
            </div>

            <div class="flex-1 form-group">
                <label for="estado">Estado:</label>
                <select id="estado" name="estado" required>
                    <option value="activo" <?= ($data['estado'] === 'activo') ? 'selected' : '' ?>>Activo</option>
                    <option value="inactivo" <?= ($data['estado'] === 'inactivo') ? 'selected' : '' ?>>Inactivo</option>
                </select>
                <small class="text-muted">Disponibilidad en el sistema.</small>
            </div>
        </div>

        <div class="form-group mb-4">
            <label for="periodo_id">Periodo:</label>
            <select id="periodo_id" name="periodo_id" required>
                <option value="">Seleccione un periodo</option>
                <?php foreach ($periodos as $periodo): ?>
                    <option value="<?= $periodo['id'] ?>" <?= (isset($data['periodo_id']) && $data['periodo_id'] == $periodo['id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($periodo['nombre']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <small class="text-muted">Seleccione el periodo al que pertenece esta escala.</small>
        </div>

        <div class="btn-group mt-3">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-sync-alt"></i> Actualizar Escala
            </button>
            <a href="<?= URL_PATH ?>escala-cualitativa" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Volver a la Lista
            </a>
        </div>
    </form>
</div>

<?php require BASE_PATH . '/app/views/layout/footer.php'; ?>