<?php require BASE_PATH . '/app/views/layout/header.php'; ?>

<div class="escala-container">

    <div class="d-flex justify-between align-center mb-3">
        <h2>
            <i class="fas fa-folder-open"></i> Evidencias Documentales
        </h2>

        <a href="<?= URL_PATH ?>documentos/create" class="btn btn-primary">
            <i class="fas fa-upload"></i> Subir Documento
        </a>
    </div>

    <!-- Buscador -->
    <div class="search-container mb-3">
        <div class="form-group">
            <input type="text"
                id="documentoSearch"
                placeholder="🔍 Buscar por indicador, periodo o nombre de archivo..."
                class="form-control">
        </div>
    </div>

    <?php $documentos = $documentos ?? []; ?>

    <?php if (empty($documentos)): ?>
        <div class="alert alert-info">
            No hay documentos cargados.
            <a href="<?= URL_PATH ?>documentos/create">Subir uno ahora</a>
        </div>
    <?php else: ?>

        <div class="table-container">
            <table id="documentoTable">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Periodo</th>
                        <th>Indicador</th>
                        <th>Proceso</th>
                        <th>Subproceso</th>
                        <th>Código</th>
                        <th>Archivo</th>
                        <th class="text-center">Estado</th>
                        <th>Subido Por</th>
                        <th class="text-center" style="width:170px;">Acciones</th>
                    </tr>
                </thead>
                <tbody>

                    <?php foreach ($documentos as $d): ?>
                        <tr>
                            <td><?= $d['id'] ?></td>

                            <td>
                                <span class="badge badge-secondary">
                                    <?= htmlspecialchars($d['periodo_nombre'] ?? '') ?>
                                </span>
                            </td>

                            <td>
                                <span class="doc-indicador" style="font-weight:600; color: var(--primary);">
                                    <?= htmlspecialchars($d['indicador_nombre'] ?? 'N/A') ?>
                                </span>
                            </td>

                            <td><?= htmlspecialchars($d['proceso'] ?? 'N/A') ?></td>
                            <td><?= htmlspecialchars($d['subproceso'] ?? 'N/A') ?></td>
                            <td><?= htmlspecialchars($d['codigo'] ?? '') ?></td>

                            <td>
                                <i class="fas fa-file-alt text-muted"></i>
                                <?= htmlspecialchars($d['nombre_archivo'] ?? '') ?>
                            </td>

                            <td class="text-center">
                                <?php if (strtolower($d['estado'] ?? '') === 'aprobado'): ?>
                                    <span class="status-badge status-activo">
                                        <i class="fas fa-check-circle"></i> Aprobado
                                    </span>
                                <?php elseif (strtolower($d['estado'] ?? '') === 'rechazado'): ?>
                                    <span class="status-badge status-inactivo">
                                        <i class="fas fa-times-circle"></i> Rechazado
                                    </span>
                                <?php else: ?>
                                    <span class="badge badge-secondary">
                                        Pendiente
                                    </span>
                                <?php endif; ?>
                            </td>

                            <td>
                                <?= htmlspecialchars($d['subido_por_nombre'] ?? '') ?>
                            </td>

                            <td class="actions">

                                <a href="<?= URL_PATH ?>uploads/<?= $d['ruta_archivo'] ?>"
                                    target="_blank"
                                    title="Ver Documento"
                                    class="action-view">
                                    <i class="fas fa-eye"></i>
                                </a>

                                <a href="<?= URL_PATH ?>documentos/edit/<?= $d['id'] ?>"
                                    class="action-edit"
                                    title="Editar Documento">
                                    <i class="fas fa-edit"></i>
                                </a>

                                <a href="<?= URL_PATH ?>documentos/delete/<?= $d['id'] ?>"
                                    class="action-delete"
                                    title="Eliminar Documento"
                                    onclick="return confirm('¿Eliminar este documento?')">
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