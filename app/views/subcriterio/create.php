<?php require BASE_PATH . '/app/views/layout/header.php'; ?>

<div class="card">
    <h2>
        <i class="fas fa-plus-circle"></i> Nuevo Subcriterio
    </h2>
    <hr class="mb-3">

    <div class="scale-info">
        <i class="fas fa-info-circle"></i>
        <strong>Consejo:</strong> El peso del subcriterio representa su ponderación dentro del criterio seleccionado.
        Verifique que la suma total de los subcriterios no exceda el 100%.
    </div>

    <form method="POST">
        <h3 class="section-title">Definición del Subcriterio</h3>

        <!-- CRITERIO -->
        <div class="form-group mb-4">
            <label for="criterio_id">Criterio Asociado:</label>
            <select id="criterio_id" name="criterio_id" required>
                <?php $criterios = $criterios ?? []; ?>
                <?php foreach ($criterios as $c): ?>
                    <option value="<?= $c['id'] ?>">
                        <?= htmlspecialchars($c['nombre']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <small class="text-muted">
                El subcriterio dependerá del criterio seleccionado.
            </small>
        </div>

        <!-- NOMBRE Y PESO -->
        <div class="d-flex mb-4">
            <div class="flex-1 form-group">
                <label for="nombre">Nombre del Subcriterio:</label>
                <input
                    type="text"
                    id="nombre"
                    name="nombre"
                    placeholder="Ej: Calidad técnica, Puntualidad específica..."
                    required>
                <small class="text-muted">
                    Nombre descriptivo del subcriterio.
                </small>
            </div>

            <div class="flex-1 form-group">
                <label for="peso">Peso (%):</label>
                <input
                    type="number"
                    id="peso"
                    name="peso"
                    step="0.01"
                    placeholder="Ej: 15.00"
                    required>
                <small class="text-muted">
                    Porcentaje dentro del criterio padre.
                </small>
            </div>
        </div>

        <!-- BOTONES -->
        <div class="btn-group mt-3">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> Guardar Subcriterio
            </button>

            <a href="<?= URL_PATH ?>subcriterio" class="btn btn-secondary">
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