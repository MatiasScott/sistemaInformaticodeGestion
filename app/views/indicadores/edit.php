<?php require BASE_PATH . '/app/views/layout/header.php'; ?>

<div class="card">
    <h2>
        <i class="fas fa-edit"></i>
        Editar Indicador: <?= htmlspecialchars($indicador['nombre'] ?? '') ?>
    </h2>
    <hr class="mb-3">

    <div class="edit-alert">
        <i class="fas fa-exclamation-triangle"></i>
        <strong>Atención:</strong> Modificar el peso o el tipo puede afectar los cálculos asociados a este indicador.
        Verifique la coherencia antes de guardar cambios.
        <br>
        <strong>Peso asignado a otros indicadores en este subcriterio:</strong> <?= $peso_total_otros ?? 0 ?>%
        <br>
        <strong>Peso máximo que puedes asignar a este indicador:</strong> <?= $peso_disponible ?? 100 ?>%
    </div>

    <form method="POST">

        <!-- ===================== -->
        <h3 class="section-title">Clasificación</h3>

        <div class="form-group mb-4">
            <label>Subcriterio:</label>
            <input type="text"
                value="<?= htmlspecialchars($indicador['subcriterio_nombre']) ?>"
                disabled>
            <input type="hidden"
                name="subcriterio_id"
                value="<?= htmlspecialchars($indicador['subcriterio_id']) ?>">
            <small class="text-muted">
                El subcriterio no puede modificarse.
            </small>
        </div>

        <!-- ===================== -->
        <h3 class="section-title">Información General</h3>

        <div class="d-flex mb-4">
            <div class="flex-1 form-group">
                <label for="codigo">Código:</label>
                <input type="text"
                    id="codigo"
                    name="codigo"
                    value="<?= htmlspecialchars($indicador['codigo']) ?>"
                    required>
            </div>

            <div class="flex-1 form-group">
                <label for="nombre">Nombre:</label>
                <input type="text"
                    id="nombre"
                    name="nombre"
                    value="<?= htmlspecialchars($indicador['nombre']) ?>"
                    required>
            </div>
        </div>

        <div class="d-flex mb-4">
            <div class="flex-1 form-group">
                <label for="tipo">Tipo:</label>
                <select id="tipo" name="tipo" required>
                    <option value="cualitativo"
                        <?= $indicador['tipo'] == 'cualitativo' ? 'selected' : '' ?>>
                        Cualitativo
                    </option>
                    <option value="cuantitativo"
                        <?= $indicador['tipo'] == 'cuantitativo' ? 'selected' : '' ?>>
                        Cuantitativo
                    </option>
                </select>
            </div>

            <div class="flex-1 form-group">
                <label for="peso">Peso (%):</label>
                <input type="number"
                    id="peso"
                    name="peso"
                    step="0.01"
                    value="<?= htmlspecialchars($indicador['peso']) ?>"
                    required>
            </div>
        </div>

        <!-- ===================== -->
        <h3 class="section-title">Configuración Técnica</h3>

        <div class="form-group mb-4">
            <label for="formula">Fórmula:</label>
            <textarea id="formula"
                name="formula"
                rows="3"><?= htmlspecialchars($indicador['formula']) ?></textarea>
        </div>

        <div class="d-flex mb-4">
            <div class="flex-1 form-group">
                <label for="valor_estandar">Valor Estándar:</label>
                <input type="number"
                    id="valor_estandar"
                    name="valor_estandar"
                    step="0.01"
                    value="<?= htmlspecialchars($indicador['valor_estandar']) ?>">
            </div>

            <div class="flex-1 form-group">
                <label for="decimales">Decimales:</label>
                <input type="number"
                    id="decimales"
                    name="decimales"
                    value="<?= htmlspecialchars($indicador['decimales']) ?>">
            </div>
        </div>

        <!-- ===================== -->
        <h3 class="section-title">Responsables</h3>

        <div class="d-flex mb-4">
            <div class="flex-1 form-group">
                <label for="responsable_ejecucion_cargo">Responsable Ejecución:</label>
                <select id="responsable_ejecucion_cargo"
                    name="responsable_ejecucion_cargo">
                    <option value="">Seleccionar</option>
                    <?php $cargos = $cargos ?? []; ?>
                    <?php foreach ($cargos as $c): ?>
                        <option value="<?= $c['id'] ?>"
                            <?= $c['id'] == $indicador['responsable_ejecucion_cargo'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($c['nombre']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="flex-1 form-group">
                <label for="responsable_evaluacion_cargo">Responsable Evaluación:</label>
                <select id="responsable_evaluacion_cargo"
                    name="responsable_evaluacion_cargo">
                    <option value="">Seleccionar</option>
                    <?php foreach ($cargos as $c): ?>
                        <option value="<?= $c['id'] ?>"
                            <?= $c['id'] == $indicador['responsable_evaluacion_cargo'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($c['nombre']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <!-- ===================== -->
        <div class="btn-group mt-3">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-sync-alt"></i> Actualizar Indicador
            </button>

            <a href="<?= URL_PATH ?>indicadores" class="btn btn-secondary">
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