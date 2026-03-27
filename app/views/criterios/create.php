<?php require BASE_PATH . '/app/views/layout/header.php'; ?>

<div class="card">
    <h2>
        <i class="fas fa-plus-circle"></i> Nuevo Criterio
    </h2>
    <hr class="mb-3">

    <div class="scale-info">
        <i class="fas fa-info-circle"></i>
        <strong>Consejo:</strong> El peso asignado representa el porcentaje de influencia del criterio dentro del período seleccionado.
        Asegúrese de que la suma total de los pesos no exceda el 100%.
    </div>

    <form method="POST">
        <h3 class="section-title">Definición del Criterio</h3>

        <!-- PERIODO -->
        <div class="form-group mb-4">
            <label for="periodo_id">Periodo:</label>
            <select id="periodo_id" name="periodo_id" required>
                <?php $periodos = $periodos ?? []; ?>
                <?php foreach ($periodos as $p): ?>
                    <option value="<?= $p['id'] ?>">
                        <?= htmlspecialchars($p['nombre']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <small class="text-muted">
                El criterio estará asociado a este período de evaluación.
            </small>
        </div>

        <!-- NOMBRE Y PESO -->
        <div class="d-flex mb-4">
            <div class="flex-1 form-group">
                <label for="nombre">Nombre del Criterio:</label>
                <input
                    type="text"
                    id="nombre"
                    name="nombre"
                    placeholder="Ej: Puntualidad, Calidad, Trabajo en equipo..."
                    required>
                <small class="text-muted">
                    Nombre descriptivo del criterio.
                </small>
            </div>

            <div class="flex-1 form-group">
                <label for="peso">Peso (%):</label>
                <input
                    type="number"
                    id="peso"
                    name="peso"
                    step="0.01"
                    placeholder="Ej: 25.00"
                    required>
                <small class="text-muted">
                    Porcentaje de ponderación dentro del período.
                </small>
            </div>
        </div>

        <!-- BOTONES -->
        <div class="btn-group mt-3">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> Guardar Criterio
            </button>

            <a href="<?= URL_PATH ?>criterios" class="btn btn-secondary">
                <i class="fas fa-times"></i> Cancelar
            </a>
        </div>
    </form>
</div>

<!-- Modal de Error -->
<?php if (!empty($error)): ?>
<div id="errorModal" class="modal" style="display: block;">
    <div class="modal-content">
        <div class="modal-header">
            <h3><i class="fas fa-exclamation-triangle"></i> Error de Validación</h3>
            <span class="modal-close" onclick="closeModal()">&times;</span>
        </div>
        <div class="modal-body">
            <p><?= htmlspecialchars($error) ?></p>
        </div>
        <div class="modal-footer">
            <button class="btn btn-primary" onclick="closeModal()">Entendido</button>
        </div>
    </div>
</div>
<?php endif; ?>

<script>
function closeModal() {
    document.getElementById('errorModal').style.display = 'none';
}
</script>

<?php require BASE_PATH . '/app/views/layout/footer.php'; ?>