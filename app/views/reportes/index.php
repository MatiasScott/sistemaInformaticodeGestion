<?php require BASE_PATH . '/app/views/layout/header.php'; ?>

<div class="card reportes-page">
    <div class="d-flex justify-between align-center mb-3 reportes-header">
        <div>
            <h2><i class="fas fa-file-export"></i> Reportes</h2>
            <p class="text-muted">Seleccione un reporte, revise la vista previa y expórtelo en Excel o PDF.</p>
        </div>
    </div>

    <div class="reportes-grid">
        <div class="reportes-sidebar">
            <?php foreach ($reportes as $key => $item): ?>
                <a class="reporte-card <?= $reporteKey === $key ? 'active' : '' ?>" href="<?= URL_PATH ?>reportes?tipo=<?= urlencode($key) ?>">
                    <strong><?= htmlspecialchars($item['titulo']) ?></strong>
                    <span><?= htmlspecialchars($item['descripcion']) ?></span>
                </a>
            <?php endforeach; ?>
        </div>

        <div class="reportes-content">
            <div class="reportes-toolbar">
                <div>
                    <h3><?= htmlspecialchars($reporte['titulo']) ?></h3>
                    <p class="text-muted mb-0"><?= htmlspecialchars($reporte['descripcion']) ?></p>
                    <?php if (!empty($reporte['periodo'])): ?>
                        <p class="text-muted">Periodo actual detectado: <strong><?= htmlspecialchars($reporte['periodo']) ?></strong></p>
                    <?php endif; ?>
                </div>

                <div class="btn-group">
                    <a class="btn btn-secondary" href="<?= URL_PATH ?>reportes/export/<?= urlencode($reporteKey) ?>/excel">Descargar Excel</a>
                    <a class="btn btn-primary" href="<?= URL_PATH ?>reportes/export/<?= urlencode($reporteKey) ?>/pdf" target="_blank">Exportar PDF</a>
                </div>
            </div>

            <div class="reportes-summary">
                <div class="reportes-kpi">
                    <span>Total registros</span>
                    <strong><?= count($rows) ?></strong>
                </div>
                <div class="reportes-kpi">
                    <span>Generado</span>
                    <strong><?= date('d/m/Y H:i') ?></strong>
                </div>
            </div>

            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <?php foreach ($reporte['columnas'] as $label): ?>
                                <th><?= htmlspecialchars($label) ?></th>
                            <?php endforeach; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($rows)): ?>
                            <tr>
                                <td colspan="<?= count($reporte['columnas']) ?>" class="text-center">No hay registros para este reporte.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($rows as $row): ?>
                                <tr>
                                    <?php foreach (array_keys($reporte['columnas']) as $columnKey): ?>
                                        <td><?= htmlspecialchars((string)($row[$columnKey] ?? '')) ?></td>
                                    <?php endforeach; ?>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require BASE_PATH . '/app/views/layout/footer.php'; ?>
