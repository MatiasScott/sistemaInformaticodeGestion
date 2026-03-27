<?php require BASE_PATH . '/app/views/layout/header.php'; ?>

<div class="escala-container">
    <div class="d-flex justify-between align-center mb-3">
        <h2><i class="fas fa-briefcase"></i> Gestión de Cargos</h2>
        <a href="<?= URL_PATH ?>cargos/create" class="btn btn-primary">
            <i class="fas fa-plus"></i> Nuevo Cargo
        </a>
    </div>

    <div class="search-container mb-3">
        <div class="form-group">
            <input type="text" id="cargoSearch" placeholder="🔍 Buscar por nombre de cargo..." class="form-control">
        </div>
    </div>

    <?php $cargos = $cargos ?? []; ?>
    <?php if (empty($cargos)): ?>
        <div class="alert alert-info">
            No hay cargos registrados. <a href="<?= URL_PATH ?>cargos/create">Crear uno ahora</a>.
        </div>
    <?php else: ?>
        <div class="table-container">
            <table id="cargosTable">
                <thead>
                    <tr>
                        <th style="width: 80px;">ID</th>
                        <th>Nombre del Cargo</th>
                        <th>Estado</th>
                        <th class="text-center" style="width: 150px;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($cargos as $c): ?>
                        <tr>
                            <td><strong><?= $c['id'] ?></strong></td>
                            <td>
                                <span class="cargo-name"><?= htmlspecialchars($c['nombre']) ?></span>
                            </td>
                            <td>
                                <?php if (strtolower($c['estado']) == 'activo'): ?>
                                    <span class="status-badge status-activo">Activo</span>
                                <?php else: ?>
                                    <span class="status-badge status-inactivo">Inactivo</span>
                                <?php endif; ?>
                            </td>
                            <td class="actions">
                                <a href="<?= URL_PATH ?>cargos/edit/<?= $c['id'] ?>" 
                                   class="action-edit" 
                                   title="Editar Cargo">
                                   <i class="fas fa-edit"></i>
                                </a>
                                
                                <a href="<?= URL_PATH ?>cargos/delete/<?= $c['id'] ?>" 
                                   class="action-delete" 
                                   title="Eliminar Cargo" 
                                   onclick="return confirm('¿Estás seguro de eliminar el cargo: <?= $c['nombre'] ?>?')">
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