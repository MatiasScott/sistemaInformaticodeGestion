<?php require BASE_PATH . '/app/views/layout/header.php'; ?>

<div class="card">
    <h2><i class="fas fa-key"></i> Editar Permiso #<?= $permiso['id'] ?></h2>
    <hr class="mb-3">

    <div class="info-box">
        <p class="mb-0" style="color: #8a6d3b;">
            <i class="fas fa-exclamation-triangle"></i>
            <strong>Atención:</strong> Si cambias el nombre del módulo o la acción, asegúrate de actualizar también las validaciones de <code>authorize()</code> en tus controladores.
        </p>
    </div>

    <form method="POST">
        <h3 class="section-title">Modificar Definición</h3>

        <div class="d-flex mb-3">
            <div class="flex-1 form-group">
                <label>Nombre del Módulo:</label>
                <input type="text" name="modulo" value="<?= htmlspecialchars($permiso['modulo']) ?>" placeholder="Ej: usuarios, roles..." required>
                <small class="text-muted">Nombre actual del módulo al que pertenece el permiso.</small>
            </div>

            <div class="flex-1 form-group">
                <label>Acción Permitida:</label>
                <select name="accion" required>
                    <option value="ver" <?= $permiso['accion'] == 'ver' ? 'selected' : '' ?>>Ver / Listar</option>
                    <option value="leer" <?= $permiso['accion'] == 'leer' ? 'selected' : '' ?>>Leer (Solo lectura)</option>
                    <option value="crear" <?= $permiso['accion'] == 'crear' ? 'selected' : '' ?>>Crear / Insertar</option>
                    <option value="actualizar" <?= $permiso['accion'] == 'actualizar' ? 'selected' : '' ?>>Actualizar / Editar</option>
                    <option value="eliminar" <?= $permiso['accion'] == 'eliminar' ? 'selected' : '' ?>>Eliminar / Borrar</option>
                    <option value="aprobar" <?= $permiso['accion'] == 'aprobar' ? 'selected' : '' ?>>Aprobar / Validar</option>
                </select>
                <small class="text-muted">Cambia la acción asociada a este permiso.</small>
            </div>
        </div>

        <div class="btn-group mt-4">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-sync-alt"></i> Actualizar Permiso
            </button>
            <a href="<?= URL_PATH ?>permission" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Cancelar
            </a>
        </div>
    </form>
</div>

<?php require BASE_PATH . '/app/views/layout/footer.php'; ?>