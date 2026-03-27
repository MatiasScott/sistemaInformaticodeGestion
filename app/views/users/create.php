<?php require BASE_PATH . '/app/views/layout/header.php'; ?>


<div class="card">
    <h2><i class="fas fa-user-plus"></i> Crear Nuevo Usuario</h2>

    <?php if (isset($error)): ?>
        <div class="alert alert-danger">⚠️ <?= $error ?></div>
    <?php endif; ?>

    <form method="POST">
        <h3 class="section-title">Datos Personales</h3>

        <div class="d-flex gap-2">
            <div class="form-group flex-1">
                <label>Primer Nombre</label>
                <input type="text" name="primer_nombre" required>
            </div>
            <div class="form-group flex-1">
                <label>Segundo Nombre</label>
                <input type="text" name="segundo_nombre">
            </div>
        </div>

        <div class="d-flex gap-2">
            <div class="form-group flex-1">
                <label>Primer Apellido</label>
                <input type="text" name="primer_apellido" required>
            </div>
            <div class="form-group flex-1">
                <label>Segundo Apellido</label>
                <input type="text" name="segundo_apellido">
            </div>
        </div>

        <div class="d-flex gap-2">
            <div class="form-group flex-1">
                <label>Correo Electrónico</label>
                <input type="email" name="email" required>
            </div>
            <div class="form-group flex-1">
                <label>Contraseña</label>
                <input type="password" name="password" required>
            </div>
        </div>

        <div class="form-group" style="width: 25%;">
            <label>Estado</label>
            <select name="estado">
                <option value="activo" selected>Activo</option>
                <option value="inactivo">Inactivo</option>
            </select>
        </div>

        <h3 class="section-title">Asignar Roles</h3>
        <div class="checkbox-grid">
            <?php foreach ($roles as $r): ?>
                <label class="checkbox-item">
                    <input type="checkbox" name="roles[]" value="<?= $r['id'] ?>">
                    <?= $r['nombre'] ?>
                </label>
            <?php endforeach; ?>
        </div>

        <h3 class="section-title">Asignar Cargos</h3>
        <div class="checkbox-grid">
            <?php foreach ($cargos as $c): ?>
                <label class="checkbox-item">
                    <input type="checkbox" name="cargos[]" value="<?= $c['id'] ?>">
                    <?= $c['nombre'] ?>
                </label>
            <?php endforeach; ?>
        </div>

        <div class="btn-group mt-3">
            <button type="submit" class="btn btn-primary">Guardar Usuario</button>
            <a href="<?= URL_PATH ?>users" class="btn btn-secondary">Cancelar</a>
        </div>
    </form>
</div>

<?php require BASE_PATH . '/app/views/layout/footer.php'; ?>