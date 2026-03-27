<?php require BASE_PATH . '/app/views/layout/header.php'; ?>

<div class="card">

    <h2>
        <i class="fas fa-plus-circle"></i> Crear Plan de Mejora
    </h2>
    <hr class="mb-3">

    <div class="scale-info">
        <i class="fas fa-info-circle"></i>
        Registre acciones correctivas o de mejora asociadas a un indicador.
        El avance debe expresarse en porcentaje (0 – 100).
    </div>

    <form method="POST">

        <!-- ===================== -->
        <h3 class="section-title">Información General</h3>

        <div class="form-group mb-4">
            <label for="indicador_id">Indicador:</label>
            <select name="indicador_id" id="indicador_id" required>
                <?php $indicadores = $indicadores ?? []; ?>
                <?php foreach ($indicadores as $i): ?>
                    <option value="<?= $i['id'] ?>">
                        <?= htmlspecialchars($i['nombre']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group mb-4" id="actividadesSection">
            <label>Actividades:</label>
            <small class="text-muted d-block mb-2">
                Agregue varias actividades. Al marcar una actividad, quedará fija y no se podrá desmarcar.
            </small>

            <div id="actividadesList"></div>

            <button type="button" id="addActividadBtn" class="btn btn-secondary mt-2" onclick="if(window.planAgregarActividad){window.planAgregarActividad(); return false;}">
                <i class="fas fa-plus"></i> Agregar Actividad
            </button>
        </div>

        <!-- ===================== -->
        <h3 class="section-title">Cronograma</h3>

        <div class="d-flex mb-4">
            <div class="flex-1 form-group">
                <label for="fecha_inicio">Fecha Inicio:</label>
                <input type="date"
                    name="fecha_inicio"
                    id="fecha_inicio"
                    required>
            </div>

            <div class="flex-1 form-group">
                <label for="fecha_fin">Fecha Fin:</label>
                <input type="date"
                    name="fecha_fin"
                    id="fecha_fin"
                    required>
            </div>
        </div>

        <!-- ===================== -->
        <h3 class="section-title">Seguimiento</h3>

        <div class="d-flex mb-4">
            <div class="flex-1 form-group">
                <label for="peso">Peso:</label>
                <input type="number"
                    step="0.01"
                    name="peso"
                    id="peso">
            </div>

            <div class="flex-1 form-group">
                <label for="avance">Avance (%):</label>
                <input type="number"
                    step="1"
                    min="0"
                    max="100"
                    name="avance"
                    id="avance"
                    value="0">
                <small id="estadoSugerido" class="text-muted"></small>
            </div>
        </div>

        <!-- ===================== -->
        <div class="btn-group mt-3">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> Guardar Plan
            </button>

            <a href="<?= URL_PATH ?>planmejoras" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Cancelar
            </a>
        </div>

    </form>
</div>

<?php require BASE_PATH . '/app/views/layout/footer.php'; ?>