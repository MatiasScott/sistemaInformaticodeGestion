<?php require BASE_PATH . '/app/views/layout/header.php'; ?>

<div class="escala-container">
    <div class="d-flex justify-between align-center mb-3">
        <h2>Usuarios</h2>
        <a href="<?= URL_PATH ?>users/create" class="btn btn-primary">+ Nuevo Usuario</a>
    </div>

    <?php $usuarios = $usuarios ?? []; ?>
    <?php if (empty($usuarios)): ?>
        <div class="alert alert-info">
            No hay usuarios registrados. <a href="<?= URL_PATH ?>users/create">Crear uno</a>
        </div>
    <?php else: ?>
        <div class="search-container mb-3">
            <div class="form-group" style="position: relative;">
                <input type="text" id="userSearch" placeholder="🔍 Buscar por nombre, apellido, cargo o rol..." class="form-control">
            </div>
        </div>
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre Completo</th>
                        <th>Email</th>
                        <th>Roles</th>
                        <th>Cargos</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($usuarios as $u): ?>
                        <tr>
                            <td><?= $u['id'] ?></td>
                            <td>
                                <?= $u['primer_nombre'] ?>
                                <?= $u['segundo_nombre'] ?>
                                <?= $u['primer_apellido'] ?>
                                <?= $u['segundo_apellido'] ?>
                            </td>
                            <td><?= $u['email'] ?></td>
                            <td>
                                <?= !empty($u['roles']) ? $u['roles'] : '<span class="badge badge-warning">Sin Rol</span>' ?>
                            </td>
                            <td>
                                <?= !empty($u['cargos']) ? $u['cargos'] : '<span class="badge badge-warning">Sin Cargo</span>' ?>
                            </td>
                            <td>
                                <span class="badge <?= $u['estado'] === 'activo' ? 'badge-success' : 'badge-danger' ?>">
                                    <?= ucfirst($u['estado']) ?>
                                </span>
                            </td>
                            <td class="actions">
                                <a href="<?= URL_PATH ?>users/edit/<?= $u['id'] ?>"
                                    class="action-edit"
                                    title="Editar Usuario">
                                    <i class="fas fa-edit"></i>
                                </a>

                                <a href="<?= URL_PATH ?>users/delete/<?= $u['id'] ?>"
                                    class="action-delete"
                                    title="Eliminar Usuario"
                                    onclick="return confirm('¿Estás seguro de eliminar este usuario?')">
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