<?php require BASE_PATH . '/app/views/layout/header.php'; ?>

<div class="escala-container">
    <div class="d-flex justify-between align-center mb-3">
        <h2><i class="fas fa-key"></i> Gestión de Permisos</h2>
        <a href="<?= URL_PATH ?>permissions/create" class="btn btn-primary">
            <i class="fas fa-plus"></i> Nuevo Permiso
        </a>
    </div>

    <div class="search-container mb-3">
        <div class="form-group">
            <input type="text" id="permSearch" placeholder="🔍 Buscar por módulo o acción (ej: usuarios, crear)..." class="form-control">
        </div>
    </div>

    <?php $permisos = $permisos ?? []; ?>
    <?php if (empty($permisos)): ?>
        <div class="alert alert-info">
            No hay permisos definidos. <a href="<?= URL_PATH ?>permissions/create">Crear uno ahora</a>
        </div>
    <?php else: ?>
        <div class="table-container">
            <table id="permisosTable">
                <thead>
                    <tr>
                        <th style="width: 80px;">ID</th>
                        <th>Módulo</th>
                        <th>Acción / Permiso</th>
                        <th class="text-center" style="width: 150px;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($permisos as $p): ?>
                        <tr>
                            <td><strong><?= $p['id'] ?></strong></td>
                            <td>
                                <span class="badge badge-info" style="text-transform: uppercase; letter-spacing: 1px;">
                                    <i class="fas fa-cubes"></i> <?= $p['modulo'] ?>
                                </span>
                            </td>
                            <td>
                                <code style="font-size: 1rem; color: var(--primary); font-weight: 600;">
                                    <?= ucfirst($p['accion']) ?>
                                </code>
                            </td>
                            <td class="actions">
                                <a href="<?= URL_PATH ?>permissions/edit/<?= $p['id'] ?>"
                                    class="action-edit"
                                    title="Editar Permiso">
                                    <i class="fas fa-edit"></i>
                                </a>

                                <a href="<?= URL_PATH ?>permissions/delete/<?= $p['id'] ?>"
                                    class="action-delete"
                                    title="Eliminar Permiso"
                                    onclick="return confirm('¿Eliminar permiso: <?= $p['modulo'] ?>.<?= $p['accion'] ?>?')">
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