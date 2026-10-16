<?php

// Run with: php tests/EvaluacionInstitutionalResultsTest.php (requires pdo_sqlite).
if (!defined('BASE_PATH')) {
    define('BASE_PATH', dirname(__DIR__));
}
require_once BASE_PATH . '/app/models/EvaluacionIndicadorModel.php';

$pdo = new PDO('sqlite::memory:');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$pdo->sqliteCreateFunction('YEAR', static function ($date) {
    return $date === null ? null : (int) substr($date, 0, 4);
}, 1);
$pdo->exec("
    CREATE TABLE periodos (id INTEGER PRIMARY KEY, nombre TEXT, fecha_inicio TEXT, fecha_fin TEXT);
    CREATE TABLE indicadores (id INTEGER PRIMARY KEY, peso REAL);
    CREATE TABLE evaluaciones_indicador (
        periodo_id INTEGER, indicador_id INTEGER, porcentaje_obtenido REAL, estado TEXT
    );
    INSERT INTO indicadores VALUES (1, 20), (2, 60);
    INSERT INTO periodos VALUES
        (1, 'PAO NOVIEMBRE 2023-ABRIL 2024', '2023-11-13', '2024-04-07'),
        (2, 'PAO MAYO-OCTUBRE 2024', '2024-06-03', '2024-11-03'),
        (3, 'PAO NOVIEMBRE 2024-ABRIL 2025', '2024-11-11', '2025-05-11'),
        (4, 'PAO MAY-OCT 2025', '2025-06-09', '2025-10-18'),
        (5, 'PAE MAYO-JUNIO 2025', '2025-05-01', '2025-06-01'),
        (6, 'PAO NOVIEMBRE 2025-ABRIL 2026', '2025-11-17', '2026-04-12'),
        (7, 'PAO NOVIEMBRE 2027-ABRIL 2028', '2027-11-14', '2028-04-25'),
        (8, 'PAO NOVIEMBRE 2028-ABRIL 2029', '2028-11-14', '2029-04-25'),
        (9, 'PAO MAYO-OCTUBRE 2023', '2023-05-08', '2023-09-24');
    INSERT INTO evaluaciones_indicador VALUES
        (1, 1, 70, 'rechazado'),
        (2, 2, 90, 'pendiente'),
        (3, 1, 80, 'pendiente'),
        (4, 2, 100, 'aprobado'),
        (5, 1, 35, 'rechazado'),
        (5, 2, NULL, 'pendiente'),
        (6, 1, NULL, 'pendiente'),
        (7, 1, 0, 'rechazado'),
        (8, 1, 100, 'aprobado'),
        (9, 1, 100, 'aprobado');
");

$reflection = new ReflectionClass(EvaluacionIndicadorModel::class);
$model = $reflection->newInstanceWithoutConstructor();
$property = $reflection->getProperty('db');
$property->setAccessible(true);
$property->setValue($model, $pdo);

$rows = $model->getInstitutionalResults();
if (array_map('intval', array_column($rows, 'year')) !== [2024, 2025, 2028]) {
    throw new RuntimeException('Incorrect final-year grouping, range filtering or ungraded-year exclusion.');
}
foreach ([
    [0, 'resultado', 85],
    [0, 'total_peso', 80],
    [1, 'resultado', 83],
    [1, 'total_peso', 100],
    [1, 'total_porcentaje_peso', 8300],
    [2, 'resultado', 0],
] as [$index, $field, $expected]) {
    if (abs((float) $rows[$index][$field] - $expected) > 0.000001) {
        throw new RuntimeException("Incorrect {$field} for row {$index}.");
    }
}
$singleYear = $model->getInstitutionalResults(2025, 2025);
if (count($singleYear) !== 1 || (int) $singleYear[0]['year'] !== 2025) {
    throw new RuntimeException('Requested year range was not respected.');
}
if ($model->getInstitutionalResults(2026, 2026) !== []) {
    throw new RuntimeException('Ungraded evaluations must not produce an annual result.');
}

echo "Institutional annual PAO/PAE regression tests passed.\n";
