<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($reporte['titulo']) ?></title>
    <style>
        body { font-family: Arial, sans-serif; color: #111; margin: 24px; }
        h1 { margin: 0 0 8px; font-size: 22px; }
        p { margin: 0 0 10px; }
        .meta { margin-bottom: 18px; color: #555; }
        table { width: 100%; border-collapse: collapse; font-size: 12px; }
        th, td { border: 1px solid #ccc; padding: 6px 8px; text-align: left; }
        th { background: #f3f4f6; }
        .actions { margin-bottom: 16px; }
        .btn { display: inline-block; padding: 8px 12px; background: #111827; color: #fff; text-decoration: none; border-radius: 4px; }
        @media print {
            .actions { display: none; }
            body { margin: 10mm; }
        }
    </style>
</head>
<body>
    <div class="actions">
        <a href="#" class="btn" onclick="window.print(); return false;">Imprimir / Guardar como PDF</a>
    </div>

    <h1><?= htmlspecialchars($reporte['titulo']) ?></h1>
    <p><?= htmlspecialchars($reporte['descripcion']) ?></p>
    <div class="meta">
        <strong>Total registros:</strong> <?= count($rows) ?> |
        <strong>Generado:</strong> <?= date('d/m/Y H:i:s') ?>
    </div>

    <table>
        <thead>
            <tr>
                <?php foreach ($reporte['columnas'] as $label): ?>
                    <th><?= htmlspecialchars($label) ?></th>
                <?php endforeach; ?>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($rows as $row): ?>
                <tr>
                    <?php foreach (array_keys($reporte['columnas']) as $columnKey): ?>
                        <td><?= htmlspecialchars((string)($row[$columnKey] ?? '')) ?></td>
                    <?php endforeach; ?>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html>
