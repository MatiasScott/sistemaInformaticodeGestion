<?php require BASE_PATH . '/app/views/layout/header.php'; ?>

<div class="card">
    <h2><i class="fas fa-edit"></i> Editar Cargo: <?= htmlspecialchars($cargo['nombre']) ?></h2>
    <hr class="mb-3">

    <div class="edit-warning">
        <i class="fas fa-exclamation-triangle"></i>
        <strong>Nota importante:</strong> Al modificar el nombre de este cargo, los cambios se reflejarán inmediatamente en todos los usuarios que tengan este cargo asignado actualmente.
    </div>

    <form method="POST">
        <h3 class="section-title">Actualizar Información</h3>

        <div class="d-flex mb-4">
            <div class="flex-1 form-group">
                <label for="nombre">Nombre del Cargo</label>
                <input type="text" name="nombre" id="nombre"
                    value="<?= htmlspecialchars($cargo['nombre']) ?>"
                    placeholder="Ej: Jefe de Área" required>
                <small class="text-muted">Modifique el nombre oficial de la posición.</small>
            </div>

            <div class="flex-1 form-group">
                <label for="estado">Estado del Cargo</label>
                <select name="estado" id="estado" required>
                    <option value="activo" <?= $cargo['estado'] == 'activo' ? 'selected' : '' ?>>Activo (Disponible)</option>
                    <option value="inactivo" <?= $cargo['estado'] == 'inactivo' ? 'selected' : '' ?>>Inactivo (No disponible)</option>
                </select>
                <small class="text-muted">Si lo marca como inactivo, no podrá ser seleccionado en nuevos usuarios.</small>
            </div>
        </div>

        <div class="btn-group mt-3">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-sync-alt"></i> Actualizar Cargo
            </button>
            <a href="<?= URL_PATH ?>cargo" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Volver
            </a>
        </div>
    </form>
</div>

<?php require BASE_PATH . '/app/views/layout/footer.php'; ?>