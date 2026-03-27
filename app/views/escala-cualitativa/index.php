<?php require BASE_PATH . '/app/views/layout/header.php'; ?>

<div class="escala-container">
    <div class="d-flex justify-between align-center mb-3">
        <h2><i class="fas fa-chart-line"></i> Escala Cualitativa</h2>
        <a href="<?= URL_PATH ?>escala-cualitativa/create" class="btn btn-primary">
            <i class="fas fa-plus"></i> Nueva Escala
        </a>
    </div>

    <div class="search-container mb-3">
        <div class="form-group">
            <input type="text" id="escalaSearch" placeholder="🔍 Buscar por nombre o valor..." class="form-control">
        </div>
    </div>

    <?php if (empty($data)): ?>
        <div class="alert alert-info">
            No hay escalas registradas. <a href="<?= URL_PATH ?>escala-cualitativa/create">Crear una ahora</a>
        </div>
    <?php else: ?>
        <div class="table-container">
            <table id="escalaTable">
                <thead>
                    <tr>
                        <th>Nombre de la Escala</th>
                        <th class="text-center">Valor Numérico</th>
                        <th class="text-center">Periodo</th>
                        <th class="text-center">Estado</th>
                        <th class="text-center" style="width: 150px;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($data as $row): ?>
                        <tr>
                            <td>
                                <span class="escala-nombre" style="font-weight: 600; color: var(--primary);">
                                    <?= htmlspecialchars($row['nombre']) ?>
                                </span>
                            </td>
                            <td class="text-center">
                                <span class="badge badge-primary" style="font-size: 0.9rem; padding: 5px 12px;">
                                    <?= htmlspecialchars($row['valor']) ?>
                                </span>
                            </td>
                            <td class="text-center">
                                <span class="badge badge-primary" style="font-size: 0.9rem; padding: 5px 12px;">
                                    <?= htmlspecialchars($row['periodo_nombre'] ?? 'N/A') ?>
                                </span>
                            </td>
                            <td class="text-center">
                                <?php if (strtolower($row['estado']) == 'activo'): ?>
                                    <span class="status-badge status-activo">
                                        <i class="fas fa-check-circle"></i> Activo
                                    </span>
                                <?php else: ?>
                                    <span class="status-badge status-inactivo">
                                        <i class="fas fa-times-circle"></i> Inactivo
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td class="actions">
                                <a href="<?= URL_PATH ?>escala-cualitativa/edit/<?= $row['id'] ?>"
                                    class="action-edit"
                                    title="Editar Escala">
                                    <i class="fas fa-edit"></i>
                                </a>

                                <a href="<?= URL_PATH ?>escala-cualitativa/delete/<?= $row['id'] ?>"
                                    class="action-delete"
                                    title="Eliminar Escala"
                                    onclick="return confirm('¿Eliminar la escala: <?= $row['nombre'] ?>?')">
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