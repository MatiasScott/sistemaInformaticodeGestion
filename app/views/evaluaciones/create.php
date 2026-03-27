<?php require BASE_PATH . '/app/views/layout/header.php'; ?>

<div class="card">
    <h2>
        <i class="fas fa-plus-circle"></i> Nueva Evaluación
    </h2>
    <hr class="mb-3">

    <div class="scale-info">
        <i class="fas fa-info-circle"></i>
        <strong>Consejo:</strong> Verifique que el valor ingresado y el valor calculado
        correspondan al indicador seleccionado. El porcentaje obtenido debe reflejar
        el cumplimiento respecto al valor estándar.
    </div>

    <form method="POST">

        <!-- ===================== -->
        <h3 class="section-title">Clasificación</h3>

        <div class="d-flex mb-4">
            <div class="flex-1 form-group">
                <label for="periodo_id">Periodo:</label>
                <select id="periodo_id" name="periodo_id" required>
                    <?php $periodos = $periodos ?? []; ?>
                    <?php foreach ($periodos as $p): ?>
                        <option value="<?= $p['id'] ?>">
                            <?= htmlspecialchars($p['nombre']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="flex-1 form-group">
                <label for="indicador_id">Indicador:</label>
                <select id="indicador_id" name="indicador_id" required>
                    <?php $indicadores = $indicadores ?? []; ?>
                    <?php foreach ($indicadores as $i): ?>
                        <option value="<?= $i['id'] ?>">
                            <?= htmlspecialchars($i['nombre']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <!-- ===================== -->
        <h3 class="section-title">Estado y Observaciones</h3>

        <div class="form-group mb-4">
            <label for="estado">Estado:</label>
            <select id="estado" name="estado">
                <option value="pendiente">Pendiente</option>
                <option value="aprobado">Aprobado</option>
                <option value="rechazado">Rechazado</option>
            </select>
        </div>

        <div class="form-group mb-4">
            <label for="observaciones">Observaciones:</label>
            <textarea id="observaciones"
                name="observaciones"
                rows="3"></textarea>
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

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const indicadorSelect = document.getElementById('indicador_id');
        const periodoSelect = document.getElementById('periodo_id');
        const dynamicFieldsContainer = document.getElementById('dynamic-fields');

        function fetchIndicadorDetails() {
            const indicadorId = indicadorSelect.value;
            const periodoId = periodoSelect.value;

            if (indicadorId && periodoId) {
                fetch(`<?= URL_PATH ?>evaluaciones/indicador-details?indicador_id=${indicadorId}&periodo_id=${periodoId}`)
                    .then(response => response.json())
                    .then(data => {
                        dynamicFieldsContainer.innerHTML = data.html || '';
                    })
                    .catch(error => {
                        console.error('Error fetching indicador details:', error);
                        dynamicFieldsContainer.innerHTML = '';
                    });
            } else {
                dynamicFieldsContainer.innerHTML = '';
            }
        }

        indicadorSelect.addEventListener('change', fetchIndicadorDetails);
        periodoSelect.addEventListener('change', fetchIndicadorDetails);
    });
</script>