<?php require BASE_PATH . '/app/views/layout/header.php'; ?>



<div class="card">
    <h2><i class="fas fa-user-edit"></i> Editar Usuario: <?= $usuario['primer_nombre'] . ' ' . $usuario['primer_apellido'] ?></h2>

    <form method="POST">
        <h3 class="section-title">Datos Personales</h3>
        
        <div class="d-flex mb-3">
            <div class="flex-1 form-group">
                <label>Primer Nombre</label>
                <input type="text" name="primer_nombre" value="<?= $usuario['primer_nombre'] ?>" required>
            </div>
            <div class="flex-1 form-group">
                <label>Segundo Nombre</label>
                <input type="text" name="segundo_nombre" value="<?= $usuario['segundo_nombre'] ?>">
            </div>
        </div>

        <div class="d-flex mb-3">
            <div class="flex-1 form-group">
                <label>Primer Apellido</label>
                <input type="text" name="primer_apellido" value="<?= $usuario['primer_apellido'] ?>" required>
            </div>
            <div class="flex-1 form-group">
                <label>Segundo Apellido</label>
                <input type="text" name="segundo_apellido" value="<?= $usuario['segundo_apellido'] ?>">
            </div>
        </div>

        <div class="d-flex mb-3">
            <div class="flex-1 form-group">
                <label>Correo Institucional</label>
                <input type="email" name="email" value="<?= $usuario['email'] ?>" required>
            </div>
            <div class="flex-1 form-group">
                <label>Nueva Contraseña <span class="text-muted">(Dejar en blanco para no cambiar)</span></label>
                <input type="password" name="password" placeholder="********">
            </div>
        </div>

        <div class="form-group" style="width: 25%;">
            <label>Estado</label>
            <select name="estado">
                <option value="activo" <?= $usuario['estado'] == 'activo' ? 'selected' : '' ?>>Activo</option>
                <option value="inactivo" <?= $usuario['estado'] == 'inactivo' ? 'selected' : '' ?>>Inactivo</option>
            </select>
        </div>

        <h3 class="section-title">Asignar Roles</h3>
        <?php 
            $userRoles = $usuario['roles'] ?? [];
            $userRoleIds = array_column($userRoles, 'id');
        ?>
        <div class="checkbox-grid">
            <?php foreach ($rolesDisponibles as $rol): ?>
                <label class="checkbox-item">
                    <input type="checkbox" name="roles[]" value="<?= $rol['id'] ?>"
                        <?= in_array($rol['id'], $userRoleIds) ? 'checked' : '' ?>>
                    <?= $rol['nombre'] ?>
                </label>
            <?php endforeach; ?>
        </div>

        <h3 class="section-title">Asignar Cargos</h3>
        <?php 
            $userCargos = $usuario['cargos'] ?? [];
            $userCargoIds = array_column($userCargos, 'id');
        ?>
        <div class="checkbox-grid">
            <?php foreach ($cargosDisponibles as $cargo): ?>
                <label class="checkbox-item">
                    <input type="checkbox" name="cargos[]" value="<?= $cargo['id'] ?>"
                        <?= in_array($cargo['id'], $userCargoIds) ? 'checked' : '' ?>>
                    <?= $cargo['nombre'] ?>
                </label>
            <?php endforeach; ?>
        </div>

        <div class="btn-group mt-3">
            <button type="submit" class="btn btn-primary">Actualizar Usuario</button>
            <a href="<?= URL_PATH ?>users" class="btn btn-secondary">Cancelar</a>
        </div>
    </form>
</div>

<?php require BASE_PATH . '/app/views/layout/footer.php'; ?>