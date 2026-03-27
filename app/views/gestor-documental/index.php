<?php require BASE_PATH . '/app/views/layout/header.php'; ?>

<div class="escala-container">
    <div class="d-flex justify-between align-center mb-3">
        <h2><i class="fas fa-folder-open"></i> Gestor Documental</h2>
    </div>

    <div class="card mb-4" style="padding: 20px; border: 1px solid #e2e8f0; background: #f8fafc;">
        <form method="GET">
            <div class="filter-grid">
                <div class="filter-group">
                    <label>Nombre del archivo</label>
                    <input type="text" name="nombre" placeholder="Ej: Acta..." value="<?= htmlspecialchars($_GET['nombre'] ?? '') ?>">
                </div>

                <div class="filter-group">
                    <label>Proceso</label>
                    <input type="text" name="proceso" placeholder="Ej: Académico..." value="<?= htmlspecialchars($_GET['proceso'] ?? '') ?>">
                </div>

                <div class="filter-group">
                    <label>Subproceso</label>
                    <input type="text" name="codigo" placeholder="Ej: Calidad..." value="<?= htmlspecialchars($_GET['codigo'] ?? '') ?>">
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary btn-filter" style="flex: 2;">
                        <i class="fas fa-filter"></i> Filtrar
                    </button>
                    <?php if (!empty($_GET['nombre']) || !empty($_GET['proceso']) || !empty($_GET['codigo'])): ?>
                        <a href="<?= URL_PATH ?>documentos" class="btn btn-secondary btn-filter" title="Limpiar Filtros" style="flex: 1;">
                            <i class="fas fa-eraser"></i>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </form>
    </div>

    <?php
    // Aseguramos que la tabla reciba datos sin importar el nombre de la variable
    $lista = $results ?? $documentos ?? [];
    ?>

    <?php if (empty($lista)): ?>
        <div class="alert alert-info">No hay documentos para mostrar.</div>
    <?php else: ?>
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Proceso</th>
                        <th>Subproceso</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($lista as $r): ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($r['nombre_archivo']) ?></strong></td>
                            <td><?= htmlspecialchars($r['proceso']) ?></td>
                            <td><span class="badge"><?= htmlspecialchars($r['subproceso']) ?></span></td>
                            <td class="actions">

                                <!-- VER -->
                                <a href="<?= URL_PATH ?>documentos/ver/<?= $r['id'] ?>"
                                    class="action-view"
                                    target="_blank">
                                    <i class="fas fa-eye"></i> Ver
                                </a>

                                <!-- DESCARGAR -->
                                <a href="<?= URL_PATH ?>documentos/descargar/<?= $r['id'] ?>"
                                    class="action-edit">
                                    <i class="fas fa-download"></i> Descargar
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