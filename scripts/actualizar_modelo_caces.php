<?php

/**
 * Alinea criterios, subcriterios e indicadores de un período con el Modelo CACES 2024 (ISTT):
 *  - Nombres con numeración (1., 1.1, 1.1.1).
 *  - Códigos ISTS-001 ... ISTS-043.
 *  - Tipo (cualitativo/cuantitativo), fórmula, valor estándar y variables.
 *
 * Uso (local):
 *   php scripts/actualizar_modelo_caces.php                         (simulación)
 *   php scripts/actualizar_modelo_caces.php --apply                 (guarda)
 *   php scripts/actualizar_modelo_caces.php --periodo="NOMBRE" ...  (otro período)
 *
 * Uso (VPS con Docker):
 *   docker exec superar1_sig php scripts/actualizar_modelo_caces.php
 *   docker exec superar1_sig php scripts/actualizar_modelo_caces.php --apply
 *
 * Solo modifica indicadores del período indicado (por defecto "AUTOEVALUACIÓN 2025"). Cada
 * indicador se localiza por su código de importación (AE2025-NN) o por su numeración (1.1.1 ...).
 * Si falta algún indicador, hay ambigüedad o un código ISTS ya lo usa otro indicador, no guarda nada.
 * Puede ejecutarse varias veces sin duplicar.
 */

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit('Este script solo puede ejecutarse por consola.');
}

define('BASE_PATH', dirname(__DIR__));
require_once BASE_PATH . '/app/config/database.php';
require_once __DIR__ . '/modelo_caces_istt.php';

$args = array_slice($argv, 1);
$apply = in_array('--apply', $args, true);
$periodoNombre = 'AUTOEVALUACIÓN 2025';
foreach ($args as $arg) {
    if (strpos($arg, '--periodo=') === 0) {
        $periodoNombre = trim(substr($arg, strlen('--periodo=')), " \"'");
    }
}

$db = (new Database())->connect();
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

echo 'Base de datos: ' . $db->query('SELECT DATABASE()')->fetchColumn() . "\n";

$stmt = $db->prepare('SELECT id FROM periodos WHERE nombre = ?');
$stmt->execute([$periodoNombre]);
$periodoId = $stmt->fetchColumn();
if (!$periodoId) {
    fwrite(STDERR, "No existe el período \"{$periodoNombre}\". Use --periodo=\"NOMBRE EXACTO\".\n");
    exit(1);
}
echo "Período: {$periodoNombre} (id {$periodoId})\n\n";

$stmt = $db->prepare(
    'SELECT i.id, i.codigo, i.nombre, i.subcriterio_id, s.criterio_id
     FROM indicadores i
     JOIN subcriterios s ON s.id = i.subcriterio_id
     JOIN criterios c ON c.id = s.criterio_id
     WHERE c.periodo_id = ?'
);
$stmt->execute([$periodoId]);
$indicadoresPeriodo = $stmt->fetchAll();

// 1) Localizar los 43 indicadores del modelo dentro del período.
$modelo = modeloCacesIndicadores();
$encontrados = [];
$errores = [];

foreach ($modelo as $numero => [$numeracion, $nombre]) {
    $codigoImportado = 'AE2025-' . str_pad((string) $numero, 2, '0', STR_PAD_LEFT);
    $candidatos = array_values(array_filter($indicadoresPeriodo, function ($i) use ($codigoImportado, $numeracion) {
        return $i['codigo'] === $codigoImportado || strpos($i['nombre'], $numeracion . ' ') === 0;
    }));

    if (count($candidatos) === 1) {
        $encontrados[$numero] = $candidatos[0];
    } elseif (count($candidatos) === 0) {
        $errores[] = 'No encontrado: ' . modeloCacesCodigo($numero) . " {$numeracion} {$nombre}";
    } else {
        $errores[] = "Ambiguo ({$numeracion}): ids " . implode(', ', array_column($candidatos, 'id'));
    }
}

// 2) Los códigos ISTS no deben estar ocupados por indicadores ajenos a este conjunto.
$idsPropios = array_map('intval', array_column($encontrados, 'id'));
$codigosDestino = array_map('modeloCacesCodigo', array_keys($modelo));
$marcadores = implode(',', array_fill(0, count($codigosDestino), '?'));
$stmt = $db->prepare("SELECT id, codigo, nombre FROM indicadores WHERE codigo IN ({$marcadores})");
$stmt->execute($codigosDestino);
foreach ($stmt->fetchAll() as $ocupado) {
    if (!in_array((int) $ocupado['id'], $idsPropios, true)) {
        $errores[] = "El código {$ocupado['codigo']} ya lo usa otro indicador (id {$ocupado['id']}: {$ocupado['nombre']}).";
    }
}

if ($errores) {
    fwrite(STDERR, "No se aplicó ningún cambio:\n  - " . implode("\n  - ", $errores) . "\n");
    exit(1);
}

// 3) Actualizar.
$actualizarIndicador = $db->prepare(
    'UPDATE indicadores
     SET codigo = ?, nombre = ?, tipo = ?, formula = ?, valor_estandar = ?, decimales = 2
     WHERE id = ?'
);
$codigoTemporal = $db->prepare("UPDATE indicadores SET codigo = CONCAT('TMP-', id) WHERE id = ?");
$actualizarSubcriterio = $db->prepare('UPDATE subcriterios SET nombre = ? WHERE id = ?');
$actualizarCriterio = $db->prepare('UPDATE criterios SET nombre = ? WHERE id = ?');
$borrarVariables = $db->prepare('DELETE FROM indicador_variables WHERE indicador_id = ?');
$insertarVariable = $db->prepare(
    'INSERT INTO indicador_variables (indicador_id, nombre_variable, descripcion) VALUES (?, ?, ?)'
);

$db->beginTransaction();

try {
    // Evita choques con la clave única de código mientras se renumera.
    foreach ($idsPropios as $id) {
        $codigoTemporal->execute([$id]);
    }

    $criteriosHechos = [];
    $subcriteriosHechos = [];

    foreach ($modelo as $numero => [$numeracion, $nombre, $tipo, $formula, $estandar, $variables]) {
        $fila = $encontrados[$numero];
        $nuevoCodigo = modeloCacesCodigo($numero);
        [$c, $s] = explode('.', $numeracion);

        $actualizarIndicador->execute([
            $nuevoCodigo,
            "{$numeracion} {$nombre}",
            $tipo,
            $formula,
            $estandar,
            $fila['id'],
        ]);

        $borrarVariables->execute([$fila['id']]);
        foreach ($variables as $variable => $descripcion) {
            $insertarVariable->execute([$fila['id'], $variable, $descripcion]);
        }

        if (!isset($subcriteriosHechos[$fila['subcriterio_id']])) {
            $actualizarSubcriterio->execute([MODELO_CACES_SUBCRITERIOS["{$c}.{$s}"], $fila['subcriterio_id']]);
            $subcriteriosHechos[$fila['subcriterio_id']] = true;
        }
        if (!isset($criteriosHechos[$fila['criterio_id']])) {
            $actualizarCriterio->execute([MODELO_CACES_CRITERIOS[(int) $c], $fila['criterio_id']]);
            $criteriosHechos[$fila['criterio_id']] = true;
        }

        printf("%-9s %-6s %-13s %s%s\n", $nuevoCodigo, $numeracion, $tipo, $nombre, $formula ? "  =>  {$formula}" : '');
    }

    echo "\nIndicadores actualizados: " . count($encontrados) . "\n";
    echo 'Subcriterios actualizados: ' . count($subcriteriosHechos) . "\n";
    echo 'Criterios actualizados: ' . count($criteriosHechos) . "\n";

    if ($apply) {
        $db->commit();
        echo "\nCambios guardados.\n";
    } else {
        $db->rollBack();
        echo "\nSimulación: no se guardó nada. Ejecute con --apply para guardar.\n";
    }
} catch (Throwable $e) {
    $db->rollBack();
    fwrite(STDERR, 'Error: ' . $e->getMessage() . "\n");
    exit(1);
}
