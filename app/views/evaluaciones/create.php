<?php require BASE_PATH . '/app/views/layout/header.php'; ?>

<div class="card">
    <h2>
        <i class="fas fa-plus-circle"></i> Nueva Evaluación
    </h2>
    <hr class="mb-3">

    <div class="scale-info">
        <i class="fas fa-info-circle"></i>
        <strong>Consejo:</strong> Seleccione el período y el indicador. La evaluación se
        creará en estado pendiente, sin nota. Las variables o la escala cualitativa
        se completarán al editar la evaluación.
    </div>

    <form method="POST">
        <h3 class="section-title">Clasificación</h3>

        <div class="d-flex mb-4">
            <div class="flex-1 form-group">
                <label for="periodo_id">Periodo:</label>
                <select id="periodo_id" name="periodo_id" required>
                    <?php foreach (($periodos ?? []) as $p): ?>
                        <option value="<?= $p['id'] ?>"
                            <?= (string) ($formData['periodo_id'] ?? '') === (string) $p['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($p['nombre']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="flex-1 form-group">
                <label for="indicador_id">Indicador:</label>
                <select id="indicador_id" name="indicador_id" required>
                    <?php foreach (($indicadores ?? []) as $i): ?>
                        <option value="<?= $i['id'] ?>"
                            <?= (string) ($formData['indicador_id'] ?? '') === (string) $i['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($i['nombre']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <h3 class="section-title">Observaciones</h3>
        <div class="form-group mb-4">
            <label for="observaciones">Observaciones (opcional):</label>
            <textarea id="observaciones" name="observaciones" rows="3"><?= htmlspecialchars($formData['observaciones'] ?? '') ?></textarea>
        </div>

        <div class="btn-group mt-3">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> Guardar Evaluación
            </button>
            <a href="<?= URL_PATH ?>evaluaciones" class="btn btn-secondary">
                <i class="fas fa-times"></i> Cancelar
            </a>
        </div>
    </form>
</div>

<?php require BASE_PATH . '/app/views/layout/footer.php'; ?>

<?php if (!empty($error)): ?>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        showWarningModal('Evaluación repetida', <?= json_encode($error, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>);
    });
</script>
<?php endif; ?>
