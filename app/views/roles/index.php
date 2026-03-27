<?php require BASE_PATH . '/app/views/layout/header.php'; ?>

<div class="escala-container">
    <div class="d-flex justify-between align-center mb-3">
        <h2><i class="fas fa-user-shield"></i> Gestión de Roles</h2>
        <a href="<?= URL_PATH ?>roles/create" class="btn btn-primary">
            <i class="fas fa-plus"></i> Crear Rol
        </a>
    </div>

    <div class="search-container mb-3">
        <div class="form-group">
            <input type="text" id="roleSearch" placeholder="🔍 Buscar por nombre de rol..." class="form-control">
        </div>
    </div>

    <?php $roles = $roles ?? []; ?>
    <?php if (empty($roles)): ?>
        <div class="alert alert-info">
            No hay roles definidos. <a href="<?= URL_PATH ?>roles/create">Crear uno ahora</a>.
        </div>
    <?php else: ?>
        <div class="table-container">
            <table id="rolesTable">
                <thead>
                    <tr>
                        <th style="width: 80px;">ID</th>
                        <th>Nombre del Rol</th>
                        <th>Cantidad de Permisos</th>
                        <th class="text-center" style="width: 150px;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($roles as $r): ?>
                        <tr>
                            <td><strong><?= $r['id'] ?></strong></td>
                            <td>
                                <span class="role-name"><?= $r['nombre'] ?></span>
                            </td>
                            <td>
                                <span class="badge badge-info">
                                    <i class="fas fa-lock"></i> <?= $r['total_permisos'] ?? 0 ?> permisos
                                </span>
                            </td>
                            <td class="actions">
                                <a href="<?= URL_PATH ?>roles/edit/<?= $r['id'] ?>"
                                    class="action-edit"
                                    title="Editar Rol">
                                    <i class="fas fa-edit"></i>
                                </a>

                                <a href="<?= URL_PATH ?>roles/delete/<?= $r['id'] ?>"
                                    class="action-delete"
                                    title="Eliminar Rol"
                                    onclick="return confirm('¿Estás seguro de eliminar el rol: <?= $r['nombre'] ?>?')">
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