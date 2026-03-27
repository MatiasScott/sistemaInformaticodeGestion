<?php require BASE_PATH . '/app/views/layout/header.php'; ?>

<div class="escala-container">
    <div class="d-flex justify-between align-center mb-3">
        <h2><i class="fas fa-clipboard-check"></i> Evaluaciones de Indicadores</h2>
        <a href="<?= URL_PATH ?>evaluaciones/create" class="btn btn-primary">
            <i class="fas fa-plus"></i> Nueva Evaluación
        </a>
    </div>

    <?php if (!empty($_GET['msg']) && $_GET['msg'] === 'bloqueada'): ?>
        <div class="alert alert-warning mb-3">
            Esta evaluación ya fue editada y quedó bloqueada para nuevas modificaciones.
        </div>
    <?php endif; ?>

    <!-- Buscador -->
    <div class="search-container mb-3">
        <div class="form-group">
            <input type="text"
                id="evaluacionSearch"
                placeholder="🔍 Buscar por indicador, periodo o evaluador..."
                class="form-control">
        </div>
    </div>

    <?php $evaluaciones = $evaluaciones ?? []; ?>

    <?php if (empty($evaluaciones)): ?>
        <div class="alert alert-info">
            No hay evaluaciones registradas.
            <a href="<?= URL_PATH ?>evaluaciones/create">Crear una ahora</a>
        </div>
    <?php else: ?>
        <div class="table-container">
            <table id="evaluacionTable">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Periodo</th>
                        <th>Indicador</th>
                        <th class="text-center">Valor Ingresado</th>
                        <th class="text-center">Valor Calculado</th>
                        <th class="text-center">% Obtenido</th>
                        <th class="text-center">Diferencia vs Meta</th>
                        <th class="text-center">Estado</th>
                        <th>Evaluado Por</th>
                        <th class="text-center" style="width:150px;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($evaluaciones as $ev): ?>
                        <tr>
                            <td><?= $ev['id'] ?></td>

                            <td>
                                <span class="badge badge-secondary">
                                    <?= htmlspecialchars($ev['periodo_nombre']) ?>
                                </span>
                            </td>

                            <td>
                                <span class="evaluacion-indicador" style="font-weight:600; color: var(--primary);">
                                    <?= htmlspecialchars($ev['indicador_nombre']) ?>
                                </span>
                            </td>

                            <td class="text-center">
                                <?php
                                $valorIngresado = (string)($ev['valor_ingresado'] ?? '');
                                $esCualitativoLegacy = $valorIngresado === '{"tipo":"cualitativo"}';
                                ?>
                                <?php if (trim($valorIngresado) === '' || $esCualitativoLegacy): ?>
                                    <span class="badge badge-secondary">A la espera de evaluacion</span>
                                <?php else: ?>
                                    <?= htmlspecialchars($valorIngresado) ?>
                                <?php endif; ?>
                            </td>

                            <td class="text-center">
                                <?= htmlspecialchars($ev['valor_calculado']) ?>
                            </td>

                            <td class="text-center">
                                <span class="badge badge-primary">
                                    <?= htmlspecialchars($ev['porcentaje_obtenido']) ?>%
                                </span>
                            </td>

                            <td class="text-center">
                                <?php $dif = (float)($ev['diferencia'] ?? 0); ?>
                                <?php if ($dif > 0): ?>
                                    <span class="status-badge status-activo" title="Supera la meta">
                                        +<?= htmlspecialchars(number_format($dif, 2)) ?>
                                    </span>
                                <?php elseif ($dif < 0): ?>
                                    <span class="status-badge status-inactivo" title="Por debajo de la meta">
                                        <?= htmlspecialchars(number_format($dif, 2)) ?>
                                    </span>
                                <?php else: ?>
                                    <span class="badge badge-secondary">0.00</span>
                                <?php endif; ?>
                            </td>

                            <td class="text-center">
                                <?php if (strtolower($ev['estado']) == 'aprobado'): ?>
                                    <span class="status-badge status-activo">
                                        <i class="fas fa-check-circle"></i> Aprobado
                                    </span>
                                <?php else: ?>
                                    <span class="status-badge status-inactivo">
                                        <i class="fas fa-clock"></i> Pendiente
                                    </span>
                                <?php endif; ?>
                            </td>

                            <td>
                                <?= htmlspecialchars($ev['evaluado_por_nombre']) ?>
                            </td>

                            <td class="actions">
                                <?php if (empty($ev['bloqueada'])): ?>
                                    <a href="<?= URL_PATH ?>evaluaciones/edit/<?= $ev['id'] ?>"
                                        class="action-edit"
                                        title="Editar Evaluación">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                <?php else: ?>
                                    <span class="action-edit" title="Evaluación bloqueada" style="opacity: .5; cursor: not-allowed;">
                                        <i class="fas fa-lock"></i>
                                    </span>
                                <?php endif; ?>

                                <a href="<?= URL_PATH ?>evaluaciones/delete/<?= $ev['id'] ?>"
                                    class="action-delete"
                                    title="Eliminar Evaluación"
                                    onclick="return confirm('¿Eliminar esta evaluación?')">
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