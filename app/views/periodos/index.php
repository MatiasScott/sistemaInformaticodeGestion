<?php require BASE_PATH . '/app/views/layout/header.php'; ?>

<div class="escala-container">
    <div class="d-flex justify-between align-center mb-3">
        <h2><i class="fas fa-calendar-alt"></i> Gestión de Periodos</h2>
        <a href="<?= URL_PATH ?>periodos/create" class="btn btn-primary">
            <i class="fas fa-plus"></i> Nuevo Periodo
        </a>
    </div>

    <div class="search-container mb-3">
        <div class="form-group">
            <input type="text" id="periodoSearch" placeholder="🔍 Buscar por nombre de periodo..." class="form-control">
        </div>
    </div>

    <?php $periodos = $periodos ?? []; ?>
    <?php if (empty($periodos)): ?>
        <div class="alert alert-info">
            No hay periodos registrados. <a href="<?= URL_PATH ?>periodos/create">Crear uno ahora</a>.
        </div>
    <?php else: ?>
        <div class="table-container">
            <table id="periodosTable">
                <thead>
                    <tr>
                        <th style="width: 60px;">ID</th>
                        <th>Nombre del Periodo</th>
                        <th>Fecha Inicio</th>
                        <th>Fecha Fin</th>
                        <th>Estado</th>
                        <th class="text-center" style="width: 150px;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($periodos as $p): ?>
                        <tr>
                            <td><strong>#<?= $p['id'] ?></strong></td>
                            <td>
                                <span class="periodo-name" style="font-weight: 600; color: var(--primary);">
                                    <?= htmlspecialchars($p['nombre']) ?>
                                </span>
                            </td>
                            <td>
                                <i class="far fa-calendar-check text-muted"></i> 
                                <?= date('d/m/Y', strtotime($p['fecha_inicio'])) ?>
                            </td>
                            <td>
                                <i class="far fa-calendar-times text-muted"></i> 
                                <?= date('d/m/Y', strtotime($p['fecha_fin'])) ?>
                            </td>
                            <td>
                                <?php if (strtolower($p['estado']) == 'activo'): ?>
                                    <span class="status-badge status-activo">
                                        <i class="fas fa-play-circle"></i> Activo
                                    </span>
                                <?php else: ?>
                                    <span class="status-badge status-inactivo">
                                        <i class="fas fa-stop-circle"></i> Inactivo
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td class="actions">
                                <a href="<?= URL_PATH ?>periodos/edit/<?= $p['id'] ?>" 
                                   class="action-edit" 
                                   title="Editar Periodo">
                                   <i class="fas fa-edit"></i>
                                </a>
                                
                                <a href="<?= URL_PATH ?>periodos/delete/<?= $p['id'] ?>" 
                                   class="action-delete" 
                                   title="Eliminar Periodo" 
                                   onclick="return confirm('¿Estás seguro de eliminar el periodo: <?= $p['nombre'] ?>?')">
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