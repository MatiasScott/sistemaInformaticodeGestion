<?php require BASE_PATH . '/app/views/layout/header.php'; ?>

<div class="escala-container">
    <div class="d-flex justify-between align-center mb-3">
        <h2><i class="fas fa-tasks"></i> Gestión de Criterios</h2>
        <a href="<?= URL_PATH ?>criterios/create" class="btn btn-primary">
            <i class="fas fa-plus"></i> Nuevo Criterio
        </a>
    </div>

    <!-- Buscador -->
    <div class="search-container mb-3">
        <div class="form-group">
            <input type="text" id="criterioSearch" placeholder="🔍 Buscar por nombre, periodo o peso..." class="form-control">
        </div>
    </div>

    <?php $criterios = $criterios ?? []; ?>

    <!-- Resumen por período -->
    <?php if (!empty($periodos_totales)): ?>
        <div class="mb-4">
            <h4>Resumen de Pesos por Período</h4>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Período</th>
                            <th class="text-center">Peso Asignado</th>
                            <th class="text-center">Peso Restante</th>
                            <th class="text-center">Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($periodos_totales as $pt): ?>
                            <tr>
                                <td><?= htmlspecialchars($pt['nombre']) ?></td>
                                <td class="text-center">
                                    <span class="badge badge-primary">
                                        <?= $pt['total'] ?> de 100%
                                    </span>
                                </td>
                                <td class="text-center">
                                    <span class="badge badge-secondary">
                                        <?= 100 - $pt['total'] ?>%
                                    </span>
                                </td>
                                <td class="text-center">
                                    <?php if ($pt['total'] == 100): ?>
                                        <span class="status-badge status-activo">
                                            <i class="fas fa-check-circle"></i> Completo
                                        </span>
                                    <?php elseif ($pt['total'] > 100): ?>
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

    <?php if (empty($criterios)): ?>
        <div class="alert alert-info">
            No hay criterios registrados.
            <a href="<?= URL_PATH ?>criterios/create">Crear uno ahora</a>
        </div>
    <?php else: ?>
        <div class="table-container">
            <table id="criterioTable">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Periodo</th>
                        <th>Nombre</th>
                        <th class="text-center">Peso (%)</th>
                        <th class="text-center">Avance (%)</th>
                        <th class="text-center">Estado</th>
                        <th class="text-center" style="width:150px;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($criterios as $c): ?>
                        <tr>
                            <td><?= $c['id'] ?></td>

                            <td>
                                <span class="badge badge-secondary">
                                    <?= htmlspecialchars($c['periodo_nombre']) ?>
                                </span>
                            </td>

                            <td>
                                <span class="criterio-nombre" style="font-weight:600; color: var(--primary);">
                                    <?= htmlspecialchars($c['nombre']) ?>
                                </span>
                            </td>

                            <td class="text-center">
                                <span class="badge badge-primary">
                                    <?= htmlspecialchars($c['peso']) ?>%
                                </span>
                            </td>

                            <td class="text-center">
                                <span class="badge badge-success">
                                    <?= htmlspecialchars($c['avance'] ?? 0) ?>%
                                </span>
                            </td>

                            <td class="text-center">
                                <?php if (strtolower($c['estado']) == 'activo'): ?>
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
                                <a href="<?= URL_PATH ?>criterios/edit/<?= $c['id'] ?>"
                                    class="action-edit"
                                    title="Editar Criterio">
                                    <i class="fas fa-edit"></i>
                                </a>

                                <a href="<?= URL_PATH ?>criterios/delete/<?= $c['id'] ?>"
                                    class="action-delete"
                                    title="Eliminar Criterio"
                                    onclick="return confirm('¿Eliminar el criterio: <?= htmlspecialchars($c['nombre']) ?>?')">
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