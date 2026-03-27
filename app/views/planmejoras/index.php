<?php require BASE_PATH . '/app/views/layout/header.php'; ?>

<?php
function renderActividadesHtml($actividadRaw)
{
    $raw = trim((string)$actividadRaw);
    if ($raw === '') {
        return '<span class="text-muted">Sin actividades</span>';
    }

    $decoded = json_decode($raw, true);
    if (is_array($decoded) && isset($decoded['actividades']) && is_array($decoded['actividades'])) {
        $decoded = $decoded['actividades'];
    }
    if (!is_array($decoded)) {
        return htmlspecialchars($raw);
    }

    $items = [];
    foreach ($decoded as $item) {
        if (is_string($item)) {
            $desc = trim($item);
            if ($desc === '') {
                continue;
            }
            $checked = false;
        } else {
            if (!is_array($item)) {
                continue;
            }
            $desc = trim((string)($item['descripcion'] ?? $item['descripcion_actividad'] ?? ''));
            if ($desc === '') {
                continue;
            }
            $checked = !empty($item['checked']);
        }
        $items[] = '<li>'
            . '<input type="checkbox" ' . ($checked ? 'checked ' : '') . 'disabled>'
            . '<span>' . htmlspecialchars($desc) . '</span>'
            . '</li>';
    }

    if (empty($items)) {
        return '<span class="text-muted">Sin actividades</span>';
    }

    return '<ul class="actividad-resumen">' . implode('', $items) . '</ul>';
}
?>

<div class="escala-container">

    <div class="d-flex justify-between align-center mb-3">
        <h2>
            <i class="fas fa-tasks"></i> Planes de Mejora
        </h2>

        <a href="<?= URL_PATH ?>planmejoras/create" class="btn btn-primary">
            <i class="fas fa-plus"></i> Nuevo Plan de Mejora
        </a>
    </div>

    <!-- Buscador -->
    <div class="search-container mb-3">
        <div class="form-group">
            <input type="text"
                id="planSearch"
                placeholder="🔍 Buscar por indicador o actividad..."
                class="form-control">
        </div>
    </div>

    <?php $planmejoras = $planmejoras ?? []; ?>

    <?php if (empty($planmejoras)): ?>
        <div class="alert alert-info">
            No hay planes de mejora registrados.
            <a href="<?= URL_PATH ?>planmejoras/create">Crear uno ahora</a>
        </div>
    <?php else: ?>

        <div class="table-container">
            <table id="planTable">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Indicador</th>
                        <th>Actividad</th>
                        <th>Fecha Inicio</th>
                        <th>Fecha Fin</th>
                        <th class="text-center">Peso</th>
                        <th class="text-center">Avance</th>
                        <th class="text-center">Estado</th>
                        <th class="text-center" style="width:150px;">Acciones</th>
                    </tr>
                </thead>
                <tbody>

                    <?php foreach ($planmejoras as $pm): ?>
                        <tr>
                            <td><?= $pm['id'] ?></td>

                            <td>
                                <span class="plan-indicador" style="font-weight:600; color: var(--primary);">
                                    <?= htmlspecialchars($pm['indicador_nombre']) ?>
                                </span>
                            </td>

                            <td>
                                <?= renderActividadesHtml($pm['actividad']) ?>
                            </td>

                            <td><?= htmlspecialchars($pm['fecha_inicio']) ?></td>
                            <td><?= htmlspecialchars($pm['fecha_fin']) ?></td>

                            <td class="text-center">
                                <span class="badge badge-secondary">
                                    <?= htmlspecialchars($pm['peso']) ?>
                                </span>
                            </td>

                            <td class="text-center" style="min-width:120px;">
                                <?php $avance = (int)$pm['avance']; ?>
                                <div class="progress-bar-container">
                                    <div class="progress-bar-fill"
                                        style="width: <?= $avance ?>%;">
                                    </div>
                                </div>
                                <small><?= $avance ?>%</small>
                            </td>

                            <td class="text-center">
                                <?php if (strtolower($pm['estado']) === 'finalizado'): ?>
                                    <span class="status-badge status-activo">
                                        <i class="fas fa-check-circle"></i> Finalizado
                                    </span>
                                <?php elseif (strtolower($pm['estado']) === 'en proceso' || strtolower($pm['estado']) === 'en_proceso'): ?>
                                    <span class="badge badge-primary">
                                        En Proceso
                                    </span>
                                <?php else: ?>
                                    <span class="badge badge-secondary">
                                        Pendiente
                                    </span>
                                <?php endif; ?>
                            </td>

                            <td class="actions">
                                <a href="<?= URL_PATH ?>planmejoras/edit/<?= $pm['id'] ?>"
                                    class="action-edit"
                                    title="Editar Plan">
                                    <i class="fas fa-edit"></i>
                                </a>

                                <a href="<?= URL_PATH ?>planmejoras/delete/<?= $pm['id'] ?>"
                                    class="action-delete"
                                    title="Eliminar Plan"
                                    onclick="return confirm('¿Eliminar este plan de mejora?')">
                                    <i class="fas fa-trash-alt"></i>
                                </a>
                            </td>

                        </tr>
                    <?php endforeach; ?>

                </tbody>
            </table>
        </div>

    <?php endif; ?>
</div>

<?php require BASE_PATH . '/app/views/layout/footer.php'; ?>