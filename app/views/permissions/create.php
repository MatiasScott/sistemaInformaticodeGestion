<?php require BASE_PATH . '/app/views/layout/header.php'; ?>

<div class="card">
    <h2><i class="fas fa-key"></i> Crear Nuevo Permiso</h2>
    <hr class="mb-3">

    <form method="POST">
        <h3 class="section-title">Definición del Permiso</h3>

        <div class="d-flex mb-3">
            <div class="flex-1 form-group">
                <label>Nombre del Módulo:</label>
                <input type="text" name="modulo" list="modulos-list" placeholder="Ej: usuarios, roles..." required>

                <datalist id="modulos-list">
                    <?php if (!empty($modulosExistentes)): ?>
                        <?php foreach ($modulosExistentes as $m): ?>
                            <option value="<?= $m ?>">
                            <?php endforeach; ?>
                        <?php endif; ?>
                </datalist>
                <small class="text-muted">Escriba el nombre del módulo.</small>
            </div>

            <div class="flex-1 form-group">
                <label>Acción:</label>
                <select name="accion" required>
                    <option value="ver">Ver</option>
                    <option value="crear">Crear</option>
                    <option value="actualizar">Actualizar</option>
                    <option value="eliminar">Eliminar</option>
                </select>
            </div>
        </div>

        <div class="btn-group mt-3">
            <button type="submit" class="btn btn-primary">Guardar</button>
            <a href="<?= URL_PATH ?>permission" class="btn btn-secondary">Cancelar</a>
        </div>
    </form>
</div>

<?php require BASE_PATH . '/app/views/layout/footer.php'; ?>