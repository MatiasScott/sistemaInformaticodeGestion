<?php require BASE_PATH . '/app/views/layout/header.php'; ?>

<?php
$actividadesIniciales = [];
$actividadRaw = trim((string)($planmejora['actividad'] ?? ''));

if ($actividadRaw !== '') {
    $decoded = json_decode($actividadRaw, true);
    if (is_array($decoded) && isset($decoded['actividades']) && is_array($decoded['actividades'])) {
        $decoded = $decoded['actividades'];
    }

    if (is_array($decoded)) {
        foreach ($decoded as $idx => $item) {
            if (is_string($item)) {
                $desc = trim($item);
                if ($desc === '') {
                    continue;
                }
                $actividadesIniciales[] = [
                    'key' => 'act_' . $idx,
                    'descripcion' => $desc,
                    'checked' => false,
                ];
                continue;
            }

            if (!is_array($item)) {
                continue;
            }
            $desc = trim((string)($item['descripcion'] ?? $item['descripcion_actividad'] ?? ''));
            if ($desc === '') {
                continue;
            }
            $key = trim((string)($item['key'] ?? ''));
            if ($key === '') {
                $key = 'act_' . $idx;
            }
            $actividadesIniciales[] = [
                'key' => $key,
                'descripcion' => $desc,
                'checked' => !empty($item['checked']),
            ];
        }
    }
}

if (empty($actividadesIniciales) && $actividadRaw !== '') {
    $actividadesIniciales[] = [
        'key' => uniqid('act_', true),
        'descripcion' => $actividadRaw,
        'checked' => false,
    ];
}
?>

<div class="card">

    <h2>
        <i class="fas fa-edit"></i> Editar Plan de Mejora
    </h2>
    <hr class="mb-3">

    <div class="edit-alert">
        <i class="fas fa-info-circle"></i>
        Está modificando un plan de mejora registrado.
        Verifique fechas, avance y estado antes de guardar.
    </div>

    <form method="POST">

        <!-- ===================== -->
        <h3 class="section-title">Información General</h3>

        <div class="form-group mb-4">
            <label for="indicador_id">Indicador:</label>
            <select name="indicador_id" id="indicador_id" required>
                <?php $indicadores = $indicadores ?? []; ?>
                <?php foreach ($indicadores as $i): ?>
                    <option value="<?= $i['id'] ?>"
                        <?= $i['id'] == $planmejora['indicador_id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($i['nombre']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group mb-4" id="actividadesSection">
            <label>Actividades:</label>
            <small class="text-muted d-block mb-2">
                Al marcar una actividad, quedará fija y no se podrá desmarcar.
            </small>

            <div id="actividadesList"
                data-initial='<?= htmlspecialchars(json_encode($actividadesIniciales, JSON_UNESCAPED_UNICODE), ENT_QUOTES, 'UTF-8') ?>'></div>

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
                    value="<?= htmlspecialchars($planmejora['fecha_inicio']) ?>"
                    required>
            </div>

            <div class="flex-1 form-group">
                <label for="fecha_fin">Fecha Fin:</label>
                <input type="date"
                    name="fecha_fin"
                    id="fecha_fin"
                    value="<?= htmlspecialchars($planmejora['fecha_fin']) ?>"
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
                    id="peso"
                    value="<?= htmlspecialchars($planmejora['peso']) ?>">
            </div>

            <div class="flex-1 form-group">
                <label for="avance">Avance (%):</label>
                <input type="number"
                    step="1"
                    min="0"
                    max="100"
                    name="avance"
                    id="avance"
                    value="<?= (int)$planmejora['avance'] ?>">
                <small id="estadoSugerido" class="text-muted"></small>
            </div>
        </div>

        <div class="form-group mb-4">
            <label for="estado">Estado:</label>
            <select name="estado" id="estado" disabled>
                <option value="pendiente"
                    <?= $planmejora['estado'] == 'pendiente' ? 'selected' : '' ?>>
                    Pendiente
                </option>
                <option value="en_proceso"
                    <?= $planmejora['estado'] == 'en_proceso' ? 'selected' : '' ?>>
                    En Proceso
                </option>
                <option value="finalizado"
                    <?= $planmejora['estado'] == 'finalizado' ? 'selected' : '' ?>>
                    Finalizado
                </option>
            </select>
        </div>
        <input type="hidden" name="estado_computed" value="">

        <!-- ===================== -->
        <div class="btn-group mt-3">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> Actualizar Plan
            </button>

            <a href="<?= URL_PATH ?>planmejoras" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Volver
            </a>
        </div>

    </form>
</div>

<?php require BASE_PATH . '/app/views/layout/footer.php'; ?>