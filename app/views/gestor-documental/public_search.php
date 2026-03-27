<?php require BASE_PATH . '/app/views/layout/header.php'; ?>

<div class="escala-container">
    <div class="card">
        <div class="card-header">
            <h3>Gestor Documental (Búsqueda pública)</h3>
        </div>

        <form method="GET" class="form-section">
            <div class="form-row">
                <input type="text" name="nombre" placeholder="Nombre del archivo" value="<?= htmlspecialchars($_GET['nombre'] ?? '') ?>">
                <input type="text" name="proceso" placeholder="Proceso" value="<?= htmlspecialchars($_GET['proceso'] ?? '') ?>">
                <input type="text" name="subproceso" placeholder="Subproceso" value="<?= htmlspecialchars($_GET['subproceso'] ?? '') ?>">
                <input type="text" name="codigo" placeholder="Codificación" value="<?= htmlspecialchars($_GET['codigo'] ?? '') ?>">
                <button class="btn btn-primary" type="submit">Buscar</button>
            </div>
        </form>

        <?php if (!empty($results)): ?>
            <div class="table-container mt-2">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nombre</th>
                            <th>Proceso</th>
                            <th>Subproceso</th>
                            <th>Código</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($results as $r): ?>
                            <tr>
                                <td><?= $r['id'] ?></td>
                                <td><?= htmlspecialchars($r['nombre_archivo']) ?></td>
                                <td><?= htmlspecialchars($r['proceso']) ?></td>
                                <td><?= htmlspecialchars($r['subproceso']) ?></td>
                                <td><?= htmlspecialchars($r['codigo']) ?></td>
                                <td>
                                    <?php if (!empty($r['ruta_archivo'])): ?>
                                        <a href="<?= URL_PATH ?>uploads/<?= $r['ruta_archivo'] ?>" target="_blank" rel="noopener" class="btn btn-secondary">Ver archivo</a>
                                    <?php else: ?>
                                        -
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php elseif (isset($results)): ?>
            <div class="alert alert-info mt-2">No se encontraron resultados.</div>
        <?php endif; ?>
    </div>
</div>

<?php require BASE_PATH . '/app/views/layout/footer.php'; ?>
