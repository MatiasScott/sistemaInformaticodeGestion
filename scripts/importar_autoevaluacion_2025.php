<?php

/**
 * Importa la autoevaluación 2025 desde "Fichas indicadores modelo 2025.xlsx" (hoja "Modelo").
 *
 * Uso:
 *   php scripts/importar_autoevaluacion_2025.php "C:\ruta\Fichas indicadores modelo 2025.xlsx"          (simulación)
 *   php scripts/importar_autoevaluacion_2025.php "C:\ruta\Fichas indicadores modelo 2025.xlsx" --apply  (guarda)
 *
 * Es idempotente: reutiliza el periodo, criterios, subcriterios, cargos e indicadores existentes
 * y no duplica evaluaciones ya registradas.
 */

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit('Este script solo puede ejecutarse por consola.');
}

define('BASE_PATH', dirname(__DIR__));
require_once BASE_PATH . '/app/config/database.php';
require_once __DIR__ . '/modelo_caces_istt.php';

const PERIODO_NOMBRE = 'AUTOEVALUACIÓN 2025';
const PERIODO_INICIO = '2025-01-01';
const PERIODO_FIN = '2025-12-31';
const HOJA = 'Modelo';

$args = array_slice($argv, 1);
$apply = in_array('--apply', $args, true);
$rutas = array_values(array_filter($args, fn($a) => $a !== '--apply'));
$archivo = $rutas[0] ?? (getenv('USERPROFILE') . '\\Downloads\\Fichas indicadores modelo 2025.xlsx');

if (!is_file($archivo)) {
    fwrite(STDERR, "No se encontró el archivo: {$archivo}\n");
    exit(1);
}

function leerHojaXlsx(string $archivo, string $nombreHoja): array
{
    $zip = new ZipArchive();
    if ($zip->open($archivo) !== true) {
        throw new RuntimeException('No se pudo abrir el archivo Excel.');
    }

    $compartidas = [];
    $xmlCompartidas = $zip->getFromName('xl/sharedStrings.xml');
    if ($xmlCompartidas !== false) {
        $sst = simplexml_load_string($xmlCompartidas);
        foreach ($sst->si as $si) {
            $si->registerXPathNamespace('m', 'http://schemas.openxmlformats.org/spreadsheetml/2006/main');
            $compartidas[] = implode('', array_map('strval', $si->xpath('.//m:t')));
        }
    }

    $workbook = simplexml_load_string($zip->getFromName('xl/workbook.xml'));
    $rels = simplexml_load_string($zip->getFromName('xl/_rels/workbook.xml.rels'));
    $relId = null;
    foreach ($workbook->sheets->sheet as $sheet) {
        if (trim((string) $sheet['name']) === $nombreHoja) {
            $relId = (string) $sheet->attributes('http://schemas.openxmlformats.org/officeDocument/2006/relationships')['id'];
        }
    }
    if ($relId === null) {
        throw new RuntimeException("No existe la hoja {$nombreHoja}.");
    }

    $rutaHoja = null;
    foreach ($rels->Relationship as $rel) {
        if ((string) $rel['Id'] === $relId) {
            $rutaHoja = 'xl/' . ltrim(preg_replace('#^/?xl/#', '', (string) $rel['Target']), '/');
        }
    }

    $hoja = simplexml_load_string($zip->getFromName($rutaHoja));
    $zip->close();

    $filas = [];
    foreach ($hoja->sheetData->row as $row) {
        $fila = [];
        foreach ($row->c as $c) {
            $columna = preg_replace('/\d+/', '', (string) $c['r']);
            $tipo = (string) $c['t'];
            if ($tipo === 's') {
                $valor = $compartidas[(int) $c->v] ?? '';
            } elseif ($tipo === 'inlineStr') {
                $valor = (string) $c->is->t;
            } else {
                $valor = (string) $c->v;
            }
            $fila[$columna] = trim(preg_replace('/\s+/u', ' ', $valor));
        }
        $filas[(int) $row['r']] = $fila;
    }

    return $filas;
}

function numero(string $valor, string $campo, int $fila): float
{
    if (!is_numeric($valor)) {
        throw new RuntimeException("Fila {$fila}: {$campo} no es numérico ('{$valor}').");
    }
    return (float) $valor;
}

function estadoPorPorcentaje(float $porcentaje): string
{
    if ($porcentaje >= 100) {
        return 'aprobado';
    }
    return $porcentaje >= 80 ? 'pendiente' : 'rechazado';
}

function buscarOCrear(PDO $db, string $select, array $paramsSelect, string $insert, array $paramsInsert, array &$contador, string $clave): int
{
    $stmt = $db->prepare($select);
    $stmt->execute($paramsSelect);
    $id = $stmt->fetchColumn();
    if ($id) {
        $contador[$clave . '_existentes']++;
        return (int) $id;
    }

    $db->prepare($insert)->execute($paramsInsert);
    $contador[$clave . '_creados']++;
    return (int) $db->lastInsertId();
}

// ===============================
// LECTURA Y VALIDACIÓN DEL EXCEL
// ===============================
$filas = leerHojaXlsx($archivo, HOJA);
$registros = [];

foreach ($filas as $numFila => $f) {
    if ($numFila === 1 || !isset($f['A']) || !ctype_digit($f['A'])) {
        continue;
    }

    $tipo = mb_strtolower($f['F'] ?? '');
    if (!in_array($tipo, ['cualitativo', 'cuantitativo'], true)) {
        throw new RuntimeException("Fila {$numFila}: tipo de indicador inválido.");
    }

    foreach (['B' => 'Criterio', 'C' => 'Subcriterio', 'D' => 'Responsable', 'E' => 'Indicador'] as $col => $campo) {
        if (($f[$col] ?? '') === '') {
            throw new RuntimeException("Fila {$numFila}: {$campo} vacío.");
        }
    }

    $pesoModelo = numero($f['I'] ?? '', 'Pesos Modelo', $numFila);
    $porcentajeModelo = numero($f['J'] ?? '', 'Porcentaje Obtenido', $numFila);
    $valoracion = numero($f['H'] ?? '', 'Valoración Numérica', $numFila);

    if ($pesoModelo <= 0) {
        throw new RuntimeException("Fila {$numFila}: el peso debe ser mayor a 0.");
    }

    // Nombres, códigos y fórmulas oficiales del Modelo CACES (scripts/modelo_caces_istt.php).
    $numero = (int) $f['A'];
    $modelo = modeloCacesIndicadores()[$numero] ?? null;
    if ($modelo === null) {
        throw new RuntimeException("Fila {$numFila}: el indicador {$numero} no existe en el Modelo CACES.");
    }
    [$numeracion, $nombreModelo, $tipoModelo, $formulaModelo, , $variablesModelo] = $modelo;
    if ($tipoModelo !== $tipo) {
        throw new RuntimeException("Fila {$numFila}: el Excel indica '{$tipo}' pero el Modelo CACES indica '{$tipoModelo}'.");
    }
    [$numCriterio, $numSubcriterio] = explode('.', $numeracion);

    $registros[] = [
        'fila' => $numFila,
        'numero' => $numero,
        'codigo' => modeloCacesCodigo($numero),
        'criterio' => MODELO_CACES_CRITERIOS[(int) $numCriterio],
        'subcriterio' => MODELO_CACES_SUBCRITERIOS["{$numCriterio}.{$numSubcriterio}"],
        'responsable' => $f['D'],
        'indicador' => "{$numeracion} {$nombreModelo}",
        'tipo' => $tipo,
        'formula' => $formulaModelo,
        'variables' => $variablesModelo,
        'calificacion' => $f['G'] ?? '',
        'valoracion' => $valoracion,
        'peso' => round($pesoModelo * 100, 2),
        'porcentaje' => round(($porcentajeModelo / $pesoModelo) * 100, 2),
        'carga_documental' => $f['L'] ?? '',
        'recomendaciones' => $f['M'] ?? '',
    ];
}

if (empty($registros)) {
    throw new RuntimeException('No se encontraron indicadores en la hoja Modelo.');
}

$pesoTotal = array_sum(array_column($registros, 'peso'));
if (abs($pesoTotal - 100) > 0.01) {
    throw new RuntimeException("La suma de pesos del modelo es {$pesoTotal}, se esperaba 100.");
}

// ===============================
// IMPORTACIÓN
// ===============================
$db = (new Database())->connect();
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$escalas = [];
foreach ($db->query("SELECT id, nombre, valor FROM escala_cualitativa WHERE estado = 'activo'") as $escala) {
    $escalas[number_format((float) $escala['valor'], 2, '.', '')] = $escala;
}

$contador = array_fill_keys([
    'periodo_existentes', 'periodo_creados', 'criterio_existentes', 'criterio_creados',
    'subcriterio_existentes', 'subcriterio_creados', 'cargo_existentes', 'cargo_creados',
    'indicador_existentes', 'indicador_creados', 'evaluaciones_creadas', 'evaluaciones_omitidas',
], 0);

$pesosCriterio = [];
$pesosSubcriterio = [];
foreach ($registros as $r) {
    $pesosCriterio[$r['criterio']] = ($pesosCriterio[$r['criterio']] ?? 0) + $r['peso'];
    $claveSub = $r['criterio'] . '|' . $r['subcriterio'];
    $pesosSubcriterio[$claveSub] = ($pesosSubcriterio[$claveSub] ?? 0) + $r['peso'];
}

$db->beginTransaction();

try {
    $periodoId = buscarOCrear(
        $db,
        'SELECT id FROM periodos WHERE nombre = ?',
        [PERIODO_NOMBRE],
        "INSERT INTO periodos (nombre, fecha_inicio, fecha_fin, estado) VALUES (?, ?, ?, 'inactivo')",
        [PERIODO_NOMBRE, PERIODO_INICIO, PERIODO_FIN],
        $contador,
        'periodo'
    );

    $subcriteriosTocados = [];
    $criteriosTocados = [];

    foreach ($registros as $r) {
        $criterioId = buscarOCrear(
            $db,
            'SELECT id FROM criterios WHERE periodo_id = ? AND nombre = ?',
            [$periodoId, $r['criterio']],
            "INSERT INTO criterios (periodo_id, nombre, peso, estado) VALUES (?, ?, ?, 'activo')",
            [$periodoId, $r['criterio'], round($pesosCriterio[$r['criterio']], 2)],
            $contador,
            'criterio'
        );

        $subcriterioId = buscarOCrear(
            $db,
            'SELECT id FROM subcriterios WHERE criterio_id = ? AND nombre = ?',
            [$criterioId, $r['subcriterio']],
            "INSERT INTO subcriterios (criterio_id, nombre, peso, estado) VALUES (?, ?, ?, 'activo')",
            [$criterioId, $r['subcriterio'], round($pesosSubcriterio[$r['criterio'] . '|' . $r['subcriterio']], 2)],
            $contador,
            'subcriterio'
        );

        $cargoId = buscarOCrear(
            $db,
            'SELECT id FROM cargos WHERE nombre = ?',
            [$r['responsable']],
            "INSERT INTO cargos (nombre, estado) VALUES (?, 'activo')",
            [$r['responsable']],
            $contador,
            'cargo'
        );

        $codigo = $r['codigo'];
        $esCuantitativo = $r['tipo'] === 'cuantitativo';
        $indicadoresAntes = $contador['indicador_creados'];

        $indicadorId = buscarOCrear(
            $db,
            'SELECT id FROM indicadores WHERE codigo = ?',
            [$codigo],
            "INSERT INTO indicadores (subcriterio_id, codigo, nombre, tipo, peso, formula, valor_estandar, decimales,
                responsable_ejecucion_cargo, responsable_evaluacion_cargo, estado)
             VALUES (?, ?, ?, ?, ?, ?, ?, 2, ?, NULL, 'activo')",
            [$subcriterioId, $codigo, $r['indicador'], $r['tipo'], $r['peso'], $r['formula'], $esCuantitativo ? $r['valoracion'] : null, $cargoId],
            $contador,
            'indicador'
        );

        if ($contador['indicador_creados'] > $indicadoresAntes) {
            foreach ($r['variables'] as $variable => $descripcion) {
                $db->prepare('INSERT INTO indicador_variables (indicador_id, nombre_variable, descripcion) VALUES (?, ?, ?)')
                    ->execute([$indicadorId, $variable, $descripcion]);
            }
        }

        $existe = $db->prepare('SELECT id FROM evaluaciones_indicador WHERE periodo_id = ? AND indicador_id = ?');
        $existe->execute([$periodoId, $indicadorId]);
        if ($existe->fetchColumn()) {
            $contador['evaluaciones_omitidas']++;
            continue;
        }

        if ($esCuantitativo) {
            $valorCalculado = numero($r['calificacion'], 'Calificación obtenida', $r['fila']);
            $valorIngresado = (string) $valorCalculado;
            $diferencia = $valorCalculado - $r['valoracion'];
        } else {
            $clave = number_format($r['valoracion'], 2, '.', '');
            if (!isset($escalas[$clave])) {
                throw new RuntimeException("Fila {$r['fila']}: no existe una escala cualitativa activa con valor {$clave}.");
            }
            $valorCalculado = $r['valoracion'];
            $valorIngresado = json_encode([
                'tipo' => 'cualitativo',
                'escala_id' => (int) $escalas[$clave]['id'],
                'nombre' => $escalas[$clave]['nombre'],
            ], JSON_UNESCAPED_UNICODE);
            $diferencia = $valorCalculado - 1;
        }

        $observaciones = implode("\n", array_filter([
            'Importado desde Fichas indicadores modelo 2025.xlsx.',
            'Responsable: ' . $r['responsable'],
            'Calificación en Excel: ' . $r['calificacion'],
            $r['carga_documental'] !== '' ? 'Carga documental por año: ' . $r['carga_documental'] : null,
            $r['recomendaciones'] !== '' ? 'Recomendaciones: ' . $r['recomendaciones'] : null,
        ]));

        $db->prepare(
            'INSERT INTO evaluaciones_indicador
                (periodo_id, indicador_id, valor_ingresado, valor_calculado, porcentaje_obtenido, diferencia, estado, observaciones, evaluado_por)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, NULL)'
        )->execute([
            $periodoId,
            $indicadorId,
            $valorIngresado,
            round($valorCalculado, 2),
            $r['porcentaje'],
            round($diferencia, 2),
            estadoPorPorcentaje($r['porcentaje']),
            $observaciones,
        ]);
        $contador['evaluaciones_creadas']++;

        $subcriteriosTocados[$subcriterioId] = true;
        $criteriosTocados[$criterioId] = true;
    }

    // Avances ponderados, equivalentes a SubcriterioModel/CriterioModel::calcularAvance.
    $avanceSub = $db->prepare(
        'UPDATE subcriterios s SET avance = (
            SELECT COALESCE(ROUND(SUM(COALESCE(e.porcentaje_obtenido, 0) * i.peso) / NULLIF(SUM(i.peso), 0), 2), 0)
            FROM indicadores i
            LEFT JOIN evaluaciones_indicador e ON e.indicador_id = i.id AND e.periodo_id = ?
            WHERE i.subcriterio_id = s.id
        ) WHERE s.id = ?'
    );
    foreach (array_keys($subcriteriosTocados) as $id) {
        $avanceSub->execute([$periodoId, $id]);
    }

    $avanceCrit = $db->prepare(
        'UPDATE criterios c SET avance = (
            SELECT COALESCE(ROUND(SUM(COALESCE(s.avance, 0) * s.peso) / NULLIF(SUM(s.peso), 0), 2), 0)
            FROM subcriterios s WHERE s.criterio_id = c.id
        ) WHERE c.id = ?'
    );
    foreach (array_keys($criteriosTocados) as $id) {
        $avanceCrit->execute([$id]);
    }

    $resultado = $db->prepare(
        'SELECT ROUND(SUM(e.porcentaje_obtenido * i.peso) / SUM(i.peso), 2)
         FROM evaluaciones_indicador e JOIN indicadores i ON i.id = e.indicador_id
         WHERE e.periodo_id = ? AND e.porcentaje_obtenido IS NOT NULL'
    );
    $resultado->execute([$periodoId]);
    $resultadoPeriodo = $resultado->fetchColumn();

    if ($apply) {
        $db->commit();
    } else {
        $db->rollBack();
    }
} catch (Throwable $e) {
    if ($db->inTransaction()) {
        $db->rollBack();
    }
    fwrite(STDERR, 'Error: ' . $e->getMessage() . "\nNo se guardó ningún cambio.\n");
    exit(1);
}

echo ($apply ? "IMPORTACIÓN APLICADA" : "SIMULACIÓN (no se guardaron cambios; use --apply para guardar)") . "\n";
echo 'Indicadores leídos del Excel: ' . count($registros) . "\n";
foreach ($contador as $clave => $valor) {
    echo str_pad($clave, 24) . ": {$valor}\n";
}
echo 'Resultado ponderado ' . PERIODO_NOMBRE . ": {$resultadoPeriodo} %\n";
