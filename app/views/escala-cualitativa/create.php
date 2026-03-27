<?php require BASE_PATH . '/app/views/layout/header.php'; ?>

<div class="card">
    <h2>
        <i class="fas <?= empty($data) ? 'fa-plus-circle' : 'fa-edit' ?>"></i>
        <?= empty($data) ? 'Nueva Escala Cualitativa' : 'Editar Escala: ' . htmlspecialchars($data['nombre']) ?>
    </h2>
    <hr class="mb-3">

    <div class="scale-info">
        <i class="fas fa-info-circle"></i>
        <strong>Consejo:</strong> Las escalas cualitativas permiten traducir puntajes numéricos a etiquetas descriptivas (ej: 100 = Excelente). Asegúrese de que los valores no se dupliquen.
    </div>

    <form method="POST">
        <h3 class="section-title">Definición de Escala</h3>

        <div class="form-group mb-4">
            <label for="nombre">Nombre de la Categoría:</label>
            <input
                type="text"
                id="nombre"
                name="nombre"
                placeholder="Ej: Excelente, Sobresaliente, Insuficiente..."
                value="<?= htmlspecialchars($data['nombre'] ?? '') ?>"
                required
                style="width: 100%;">
            <small class="text-muted">Nombre descriptivo que aparecerá en los reportes.</small>
        </div>

        <div class="d-flex mb-4">
            <div class="flex-1 form-group">
                <label for="valor">Valor Numérico Asignado:</label>
                <input
                    type="number"
                    id="valor"
                    name="valor"
                    placeholder="Ej: 95.00"
                    value="<?= htmlspecialchars($data['valor'] ?? '') ?>"
                    step="0.01"
                    required>
                <small class="text-muted">Use decimales si es necesario (ej: 4.5).</small>
            </div>

            <div class="flex-1 form-group">
                <label for="estado">Estado de la Escala:</label>
                <select id="estado" name="estado" required>
                    <option value="activo" <?= (isset($data['estado']) && $data['estado'] === 'activo') ? 'selected' : '' ?>>Activo</option>
                    <option value="inactivo" <?= (isset($data['estado']) && $data['estado'] === 'inactivo') ? 'selected' : '' ?>>Inactivo</option>
                </select>
                <small class="text-muted">Las escalas inactivas no se podrán usar en nuevos cálculos.</small>
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
                <i class="fas fa-save"></i> <?= empty($data) ? 'Crear Escala' : 'Guardar Cambios' ?>
            </button>
            <a href="<?= URL_PATH ?>escala-cualitativa" class="btn btn-secondary">
                <i class="fas fa-times"></i> Cancelar
            </a>
        </div>
    </form>
</div>

<?php require BASE_PATH . '/app/views/layout/footer.php'; ?>