<?php require BASE_PATH . '/app/views/layout/header.php'; ?>

<div class="escala-container">
    <div class="d-flex justify-between align-center mb-3">
        <h2><i class="fas fa-chart-bar"></i> Gestión de Indicadores</h2>
        <a href="<?= URL_PATH ?>indicadores/create" class="btn btn-primary">
            <i class="fas fa-plus"></i> Nuevo Indicador
        </a>
    </div>

    <!-- Buscador -->
    <div class="search-container mb-3">
        <div class="form-group">
            <input type="text" id="indicadorSearch"
                placeholder="🔍 Buscar por código, nombre, criterio, responsable..."
                class="form-control">
        </div>
    </div>

    <?php $indicadores = $indicadores ?? []; ?>

    <!-- Resumen por subcriterio -->
    <?php if (!empty($subcriterios_totales)): ?>
        <div class="mb-4">
            <h4>Resumen de Pesos por Subcriterio</h4>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Subcriterio</th>
                            <th class="text-center">Peso Asignado</th>
                            <th class="text-center">Peso Restante</th>
                            <th class="text-center">Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($subcriterios_totales as $st): ?>
                            <tr>
                                <td><?= htmlspecialchars($st['nombre']) ?></td>
                                <td class="text-center">
                                    <span class="badge badge-primary">
                                        <?= $st['total'] ?> de 100%
                                    </span>
                                </td>
                                <td class="text-center">
                                    <span class="badge badge-secondary">
                                        <?= 100 - $st['total'] ?>%
                                    </span>
                                </td>
                                <td class="text-center">
                                    <?php if ($st['total'] == 100): ?>
                                        <span class="status-badge status-activo">
                                            <i class="fas fa-check-circle"></i> Completo
                                        </span>
                                    <?php elseif ($st['total'] > 100): ?>
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

    <?php if (empty($indicadores)): ?>
        <div class="alert alert-info">
            No hay indicadores registrados.
            <a href="<?= URL_PATH ?>indicadores/create">Crear uno ahora</a>
        </div>
    <?php else: ?>
        <div class="table-container">
            <table id="indicadorTable">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Periodo</th>
                        <th>Criterio</th>
                        <th>Subcriterio</th>
                        <th>Código</th>
                        <th>Nombre</th>
                        <th>Tipo</th>
                        <th class="text-center">Peso (%)</th>
                        <th class="text-center">Valor Estándar</th>
                        <th>Resp. Ejecución</th>
                        <th>Resp. Evaluación</th>
                        <th class="text-center">Estado</th>
                        <th class="text-center" style="width:150px;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($indicadores as $i): ?>
                        <tr>
                            <td><?= $i['id'] ?></td>

                            <td><span class="badge badge-secondary">
                                    <?= htmlspecialchars($i['periodo_nombre']) ?>
                                </span></td>

                            <td><?= htmlspecialchars($i['criterio_nombre']) ?></td>
                            <td><?= htmlspecialchars($i['subcriterio_nombre']) ?></td>

                            <td>
                                <span class="badge badge-dark">
                                    <?= htmlspecialchars($i['codigo']) ?>
                                </span>
                            </td>

                            <td>
                                <span class="indicador-nombre" style="font-weight:600; color: var(--primary);">
                                    <?= htmlspecialchars($i['nombre']) ?>
                                </span>
                            </td>

                            <td>
                                <span class="badge badge-info">
                                    <?= htmlspecialchars($i['tipo']) ?>
                                </span>
                            </td>

                            <td class="text-center">
                                <span class="badge badge-primary">
                                    <?= htmlspecialchars($i['peso']) ?>%
                                </span>
                            </td>

                            <td class="text-center">
                                <?= htmlspecialchars($i['valor_estandar']) ?>
                            </td>

                            <td><?= htmlspecialchars($i['responsable_ejecucion_cargo']) ?></td>
                            <td><?= htmlspecialchars($i['responsable_evaluacion_cargo']) ?></td>

                            <td class="text-center">
                                <?php if (strtolower($i['estado']) == 'activo'): ?>
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
                                <a href="<?= URL_PATH ?>indicadores/edit/<?= $i['id'] ?>"
                                    class="action-edit"
                                    title="Editar Indicador">
                                    <i class="fas fa-edit"></i>
                                </a>

                                <a href="<?= URL_PATH ?>indicadores/delete/<?= $i['id'] ?>"
                                    class="action-delete"
                                    title="Eliminar Indicador"
                                    onclick="return confirm('¿Eliminar el indicador: <?= htmlspecialchars($i['nombre']) ?>?')">
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