<?php require BASE_PATH . '/app/views/layout/header.php'; ?>

<div class="escala-container">
    <div class="d-flex justify-between align-center mb-3">
        <h2><i class="fas fa-layer-group"></i> Gestión de Subcriterios</h2>
        <a href="<?= URL_PATH ?>subcriterio/create" class="btn btn-primary">
            <i class="fas fa-plus"></i> Nuevo Subcriterio
        </a>
    </div>

    <!-- Buscador -->
    <div class="search-container mb-3">
        <div class="form-group">
            <input type="text" id="subcriterioSearch"
                placeholder="🔍 Buscar por nombre, criterio o peso..."
                class="form-control">
        </div>
    </div>

    <?php $subcriterios = $subcriterios ?? []; ?>

    <!-- Resumen por criterio -->
    <?php if (!empty($criterios_totales)): ?>
        <div class="mb-4">
            <h4>Resumen de Pesos por Criterio</h4>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Criterio</th>
                            <th class="text-center">Peso Asignado</th>
                            <th class="text-center">Peso Restante</th>
                            <th class="text-center">Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($criterios_totales as $ct): ?>
                            <tr>
                                <td><?= htmlspecialchars($ct['nombre']) ?></td>
                                <td class="text-center">
                                    <span class="badge badge-primary">
                                        <?= $ct['total'] ?> de 100%
                                    </span>
                                </td>
                                <td class="text-center">
                                    <span class="badge badge-secondary">
                                        <?= 100 - $ct['total'] ?>%
                                    </span>
                                </td>
                                <td class="text-center">
                                    <?php if ($ct['total'] == 100): ?>
                                        <span class="status-badge status-activo">
                                            <i class="fas fa-check-circle"></i> Completo
                                        </span>
                                    <?php elseif ($ct['total'] > 100): ?>
                                        <span class="status-badge status-inactivo">
                                            <i class="fas fa-exclamation-triangle"></i> Excedido
                                        </span>
                                    <?php else: ?>
                                        <span class="status-badge status-pendiente">
                                            <i class="fas fa-clock"></i> Incompleto
                                        </span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>

    <?php if (empty($subcriterios)): ?>
        <div class="alert alert-info">
            No hay subcriterios registrados.
            <a href="<?= URL_PATH ?>subcriterio/create">Crear uno ahora</a>
        </div>
    <?php else: ?>
        <div class="table-container">
            <table id="subcriterioTable">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Criterio</th>
                        <th>Nombre</th>
                        <th class="text-center">Peso (%)</th>
                        <th class="text-center">Avance (%)</th>
                        <th class="text-center">Estado</th>
                        <th class="text-center" style="width:150px;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($subcriterios as $s): ?>
                        <tr>
                            <td><?= $s['id'] ?></td>

                            <td>
                                <span class="badge badge-secondary">
                                    <?= htmlspecialchars($s['criterio_nombre']) ?>
                                </span>
                            </td>

                            <td>
                                <span class="subcriterio-nombre"
                                    style="font-weight:600; color: var(--primary);">
                                    <?= htmlspecialchars($s['nombre']) ?>
                                </span>
                            </td>

                            <td class="text-center">
                                <span class="badge badge-primary">
                                    <?= htmlspecialchars($s['peso']) ?>%
                                </span>
                            </td>

                            <td class="text-center">
                                <span class="badge badge-success">
                                    <?= htmlspecialchars($s['avance'] ?? 0) ?>%
                                </span>
                            </td>

                            <td class="text-center">
                                <?php if (strtolower($s['estado']) == 'activo'): ?>
                                    <span class="status-badge status-activo">
                                        <i class="fas fa-check-circle"></i> Activo
                                    </span>
                                <?php else: ?>
                                    <span class="status-badge status-inactivo">
                                        <i class="fas fa-times-circle"></i> Inactivo
                                    </span>
                                <?php endif; ?>
                            </td>

                            <td class="actions">
                                <a href="<?= URL_PATH ?>subcriterio/edit/<?= $s['id'] ?>"
                                    class="action-edit"
                                    title="Editar Subcriterio">
                                    <i class="fas fa-edit"></i>
                                </a>

                                <a href="<?= URL_PATH ?>subcriterio/delete/<?= $s['id'] ?>"
                                    class="action-delete"
                                    title="Eliminar Subcriterio"
                                    onclick="return confirm('¿Eliminar el subcriterio: <?= htmlspecialchars($s['nombre']) ?>?')">
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