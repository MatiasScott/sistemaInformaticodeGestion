<?php require BASE_PATH . '/app/views/layout/header.php'; ?>

<div class="card">
    <h2>
        <i class="fas fa-edit"></i>
        Editar Criterio: <?= htmlspecialchars($criterio['nombre'] ?? '') ?>
    </h2>
    <hr class="mb-3">

    <div class="edit-alert">
        <i class="fas fa-exclamation-triangle"></i>
        <strong>Atención:</strong> Modificar el peso puede afectar el cálculo final del período si la suma total supera el 100%.
        <br>
        <strong>Peso asignado a otros criterios en este período:</strong> <?= $peso_total_otros ?? 0 ?>%
        <br>
        <strong>Peso máximo que puedes asignar a este criterio:</strong> <?= $peso_disponible ?? 100 ?>%
    </div>

    <form method="POST">
        <h3 class="section-title">Actualizar Información</h3>

        <!-- PERIODO -->
        <div class="form-group mb-4">
            <label for="periodo_id">Periodo:</label>
            <select id="periodo_id" name="periodo_id" required>
                <?php $periodos = $periodos ?? []; ?>
                <?php foreach ($periodos as $p): ?>
                    <option value="<?= $p['id'] ?>"
                        <?= $p['id'] == $criterio['periodo_id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($p['nombre']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <small class="text-muted">
                El criterio seguirá perteneciendo a este período.
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
                    value="<?= htmlspecialchars($criterio['nombre']) ?>"
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
                    value="<?= htmlspecialchars($criterio['peso']) ?>"
                    required>
                <small class="text-muted">
                    Porcentaje de ponderación dentro del período.
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
                value="<?= htmlspecialchars($criterio['avance'] ?? 0) ?>"
                readonly
                style="background-color: #f8f9fa; cursor: not-allowed;">
            <small class="text-muted">
                Avance calculado automáticamente basado en los subcriterios evaluados.
            </small>
        </div>

        <!-- BOTONES -->
        <div class="btn-group mt-3">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-sync-alt"></i> Actualizar Criterio
            </button>

            <a href="<?= URL_PATH ?>criterios" class="btn btn-secondary">
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