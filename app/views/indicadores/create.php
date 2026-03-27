<?php require BASE_PATH . '/app/views/layout/header.php'; ?>

<div class="card">
    <h2>
        <i class="fas fa-plus-circle"></i> Nuevo Indicador
    </h2>
    <hr class="mb-3">

    <div class="scale-info">
        <i class="fas fa-info-circle"></i>
        <strong>Consejo:</strong> Configure correctamente el tipo, peso y valor estándar.
        Si el indicador es cuantitativo, asegúrese de definir la fórmula y los decimales adecuados.
    </div>

    <form method="POST">

        <!-- ===================== -->
        <h3 class="section-title">Clasificación</h3>

        <div class="form-group mb-4">
            <label for="subcriterio_id">Subcriterio:</label>
            <select id="subcriterio_id" name="subcriterio_id" required>
                <?php $subcriterios = $subcriterios ?? []; ?>
                <?php foreach ($subcriterios as $s): ?>
                    <option value="<?= $s['id'] ?>">
                        <?= htmlspecialchars($s['nombre']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <small class="text-muted">
                El indicador pertenecerá a este subcriterio.
            </small>
        </div>

        <!-- ===================== -->
        <h3 class="section-title">Información General</h3>

        <div class="d-flex mb-4">
            <div class="flex-1 form-group">
                <label for="codigo">Código:</label>
                <input type="text" id="codigo" name="codigo" required>
                <small class="text-muted">Identificador único del indicador.</small>
            </div>

            <div class="flex-1 form-group">
                <label for="nombre">Nombre:</label>
                <input type="text" id="nombre" name="nombre" required>
                <small class="text-muted">Nombre descriptivo del indicador.</small>
            </div>
        </div>

        <div class="d-flex mb-4">
            <div class="flex-1 form-group">
                <label for="tipo">Tipo:</label>
                <select id="tipo" name="tipo" required>
                    <option value="cualitativo">Cualitativo</option>
                    <option value="cuantitativo">Cuantitativo</option>
                </select>
            </div>

            <div class="flex-1 form-group">
                <label for="peso">Peso (%):</label>
                <input type="number" id="peso" name="peso" step="0.01" required>
                <small class="text-muted">
                    Ponderación dentro del subcriterio.
                </small>
            </div>
        </div>

        <!-- ===================== -->
        <h3 class="section-title">Configuración Técnica</h3>

        <div class="form-group mb-4">
            <label for="formula">Fórmula:</label>
            <textarea id="formula" name="formula" rows="3"></textarea>
            <small class="text-muted">
                Defina la fórmula solo si es cuantitativo.
            </small>
        </div>

        <div class="d-flex mb-4">
            <div class="flex-1 form-group">
                <label for="valor_estandar">Valor Estándar:</label>
                <input type="number" id="valor_estandar" name="valor_estandar" step="0.01">
            </div>

            <div class="flex-1 form-group">
                <label for="decimales">Decimales:</label>
                <input type="number" id="decimales" name="decimales" value="2">
            </div>
        </div>

        <!-- ===================== -->
        <h3 class="section-title">Responsables</h3>

        <div class="d-flex mb-4">
            <div class="flex-1 form-group">
                <label for="responsable_ejecucion_cargo">Responsable Ejecución:</label>
                <select id="responsable_ejecucion_cargo" name="responsable_ejecucion_cargo">
                    <option value="">Seleccionar</option>
                    <?php $cargos = $cargos ?? []; ?>
                    <?php foreach ($cargos as $c): ?>
                        <option value="<?= $c['id'] ?>">
                            <?= htmlspecialchars($c['nombre']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="flex-1 form-group">
                <label for="responsable_evaluacion_cargo">Responsable Evaluación:</label>
                <select id="responsable_evaluacion_cargo" name="responsable_evaluacion_cargo">
                    <option value="">Seleccionar</option>
                    <?php foreach ($cargos as $c): ?>
                        <option value="<?= $c['id'] ?>">
                            <?= htmlspecialchars($c['nombre']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <!-- ===================== -->
        <div class="btn-group mt-3">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> Guardar Indicador
            </button>

            <a href="<?= URL_PATH ?>indicadores" class="btn btn-secondary">
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