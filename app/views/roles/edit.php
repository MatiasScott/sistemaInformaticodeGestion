<?php require BASE_PATH . '/app/views/layout/header.php'; ?>

<div class="card">
    <h2><i class="fas fa-user-shield"></i> Editar Rol: <?= htmlspecialchars($rol['nombre']) ?></h2>
    <hr class="mb-3">

    <form method="POST">
        <h3 class="section-title">Información General</h3>
        <div class="form-group">
            <label>Nombre del Rol:</label>
            <input type="text" name="nombre" value="<?= htmlspecialchars($rol['nombre']) ?>" required style="width: 50%;">
        </div>

        <h3 class="section-title">Permisos del Rol</h3>
        <p class="text-muted mb-3">Modifica las acciones permitidas para este rol en cada módulo.</p>

        <?php
        $modulosAgrupados = [];
        foreach ($permisos as $p) {
            $modulosAgrupados[$p['modulo']][] = $p;
        }
        ?>

        <?php foreach ($modulosAgrupados as $modulo => $lista): ?>
            <?php
            // Verificar si todos los permisos de este módulo están marcados para activar el "Marcar todo" al cargar
            $idsModulo = array_column($lista, 'id');
            $estaTodoMarcado = !array_diff($idsModulo, $permisosAsignados);
            ?>
            <div class="module-container mb-3">
                <div class="module-header">
                    <span><i class="fas fa-folder-open"></i> Módulo: <?= ucfirst($modulo) ?></span>

                    <label class="select-all-label">
                        <input type="checkbox" class="select-all-module" data-modulo="<?= $modulo ?>" <?= $estaTodoMarcado ? 'checked' : '' ?>>
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
                                <?= in_array($permiso['id'], $permisosAsignados) ? 'checked' : '' ?>>
                            <?= ucfirst($permiso['accion']) ?>
                        </label>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endforeach; ?>

        <div class="btn-group mt-4">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-sync-alt"></i> Actualizar Rol
            </button>
            <a href="<?= URL_PATH ?>roles" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Cancelar
            </a>
        </div>
    </form>
</div>

<?php require BASE_PATH . '/app/views/layout/footer.php'; ?>