<?php require BASE_PATH . '/app/views/layout/header.php'; ?>

<div class="card">
    <h2><i class="fas fa-briefcase"></i> Registrar Nuevo Cargo</h2>
    <hr class="mb-3">

    <div class="form-info">
        <i class="fas fa-info-circle"></i> 
        Complete la información del cargo. Una vez creado, podrá asignarlo a los usuarios desde el módulo de Gestión de Usuarios.
    </div>

    <form method="POST">
        <h3 class="section-title">Datos del Cargo</h3>
        
        <div class="d-flex mb-4">
            <div class="flex-1 form-group">
                <label for="nombre">Nombre del Cargo</label>
                <input type="text" name="nombre" id="nombre" placeholder="Ej: Gerente de Operaciones, Analista IT..." required>
                <small class="text-muted">Este nombre será visible en los perfiles de usuario.</small>
            </div>

            <div class="flex-1 form-group">
                <label for="estado">Estado Inicial</label>
                <select name="estado" id="estado" required>
                    <option value="activo" selected>Activo (Disponible)</option>
                    <option value="inactivo">Inactivo (No disponible)</option>
                </select>
                <small class="text-muted">Determine si el cargo puede ser asignado de inmediato.</small>
            </div>
        </div>

        <div class="btn-group mt-3">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> Guardar Cargo
            </button>
            <a href="<?= URL_PATH ?>cargo" class="btn btn-secondary">
                <i class="fas fa-times"></i> Cancelar
            </a>
        </div>
    </form>
</div>

<?php require BASE_PATH . '/app/views/layout/footer.php'; ?>