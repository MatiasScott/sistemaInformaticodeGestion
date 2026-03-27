<?php require BASE_PATH . '/app/views/layout/header.php'; ?>

<div class="card">
    <h2>
        <i class="fas fa-edit"></i>
        Editar Subcriterio: <?= htmlspecialchars($subcriterio['nombre'] ?? '') ?>
    </h2>
    <hr class="mb-3">

    <div class="edit-alert">
        <i class="fas fa-exclamation-triangle"></i>
        <strong>Atención:</strong> Modificar el peso puede afectar la ponderación total del criterio asociado.
        Verifique que la suma de los subcriterios no supere el 100%.
        <br>
        <strong>Peso asignado a otros subcriterios en este criterio:</strong> <?= $peso_total_otros ?? 0 ?>%
        <br>
        <strong>Peso máximo que puedes asignar a este subcriterio:</strong> <?= $peso_disponible ?? 100 ?>%
    </div>

    <form method="POST">
        <h3 class="section-title">Actualizar Información</h3>

        <!-- CRITERIO -->
        <div class="form-group mb-4">
            <label for="criterio_id">Criterio Asociado:</label>
            <select id="criterio_id" name="criterio_id" required>
                <?php $criterios = $criterios ?? []; ?>
                <?php foreach ($criterios as $c): ?>
                    <option value="<?= $c['id'] ?>"
                        <?= $c['id'] == $subcriterio['criterio_id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($c['nombre']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <small class="text-muted">
                El subcriterio seguirá vinculado a este criterio.
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
                    value="<?= htmlspecialchars($subcriterio['nombre']) ?>"
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
                    value="<?= htmlspecialchars($subcriterio['peso']) ?>"
                    required>
                <small class="text-muted">
                    Porcentaje dentro del criterio padre.
                </small>
            </div>
        </div>

        <!-- AVANCE -->
        <div class="form-group mb-4">
            <label for="avance">Avance (%):</label>
            <input
                type="number"
                id="avance"
                name="avance"
                step="0.01"
                value="<?= htmlspecialchars($subcriterio['avance'] ?? 0) ?>"
                readonly
                style="background-color: #f8f9fa; cursor: not-allowed;">
            <small class="text-muted">
                Avance calculado automáticamente basado en los indicadores evaluados.
            </small>
        </div>

        <!-- BOTONES -->
        <div class="btn-group mt-3">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-sync-alt"></i> Actualizar Subcriterio
            </button>

            <a href="<?= URL_PATH ?>subcriterio" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Volver a la Lista
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