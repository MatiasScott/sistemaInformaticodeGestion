<?php require BASE_PATH . '/app/views/layout/header.php'; ?>

<div class="card">
    <h2><i class="fas fa-user-shield"></i> Gestionar Rol</h2>
    <hr class="mb-3">

    <form method="POST">
        <h3 class="section-title">Información General</h3>
        <div class="form-group">
            <label>Nombre del Rol:</label>
            <input type="text" name="nombre" value="<?= $rol['nombre'] ?? '' ?>" required style="width: 50%;" placeholder="Ej: Administrador de Finanzas">
        </div>

        <h3 class="section-title">Asignar Permisos</h3>

        <?php
        $modulosAgrupados = [];
        foreach ($permisos as $p) {
            $modulosAgrupados[$p['modulo']][] = $p;
        }
        ?>

        <?php foreach ($modulosAgrupados as $modulo => $lista): ?>
            <div class="module-container mb-3" id="module-<?= $modulo ?>">
                <div class="module-header">
                    <span><i class="fas fa-folder-open"></i> Módulo: <?= ucfirst($modulo) ?></span>

                    <label class="select-all-label">
                        <input type="checkbox" class="select-all-module" data-modulo="<?= $modulo ?>">
                        Marcar todo
                    </label>
                </div>

                <div class="permission-grid">
                    <?php foreach ($lista as $permiso): ?>
                        <label class="permission-item">
                            <input type="checkbox"
                                name="permisos[]"
                                class="perm-check-<?= $modulo ?>"
                                value="<?= $permiso['id'] ?>"
                                <?= (isset($permisosAsignados) && in_array($permiso['id'], $permisosAsignados)) ? 'checked' : '' ?>>
                            <?= ucfirst($permiso['accion']) ?>
                        </label>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endforeach; ?>

        <div class="btn-group mt-3">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> Guardar Cambios
            </button>
            <a href="<?= URL_PATH ?>roles" class="btn btn-secondary">Cancelar</a>
        </div>
    </form>
</div>

<?php require BASE_PATH . '/app/views/layout/footer.php'; ?>