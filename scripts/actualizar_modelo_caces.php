<?php

/**
 * Alinea criterios, subcriterios e indicadores con el Modelo CACES 2024 (ISTT):
 *  - Nombres con numeración (1., 1.1, 1.1.1).
 *  - Códigos ISTS-001 ... ISTS-043.
 *  - Tipo (cualitativo/cuantitativo), fórmula, valor estándar y variables.
 *
 * Uso:
 *   php scripts/actualizar_modelo_caces.php          (simulación)
 *   php scripts/actualizar_modelo_caces.php --apply  (guarda)
 *
 * Localiza cada indicador por su código de importación (AE2025-NN) o, si ya fue actualizado,
 * por su numeración (1.1.1 ...), por lo que puede ejecutarse varias veces sin duplicar nada.
 */

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit('Este script solo puede ejecutarse por consola.');
}

define('BASE_PATH', dirname(__DIR__));
require_once BASE_PATH . '/app/config/database.php';
require_once __DIR__ . '/modelo_caces_istt.php';

$apply = in_array('--apply', array_slice($argv, 1), true);

$db = (new Database())->connect();
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

$buscar = $db->prepare(
    'SELECT i.id, i.codigo, i.subcriterio_id, s.criterio_id
     FROM indicadores i
     JOIN subcriterios s ON s.id = i.subcriterio_id
     WHERE i.codigo = ? OR (i.codigo LIKE \'TMP-%\' AND i.nombre LIKE ?)
     LIMIT 1'
);
$actualizarIndicador = $db->prepare(
    'UPDATE indicadores
     SET codigo = ?, nombre = ?, tipo = ?, formula = ?, valor_estandar = ?, decimales = 2
     WHERE id = ?'
);
$actualizarSubcriterio = $db->prepare('UPDATE subcriterios SET nombre = ? WHERE id = ?');
$actualizarCriterio = $db->prepare('UPDATE criterios SET nombre = ? WHERE id = ?');
$borrarVariables = $db->prepare('DELETE FROM indicador_variables WHERE indicador_id = ?');
$insertarVariable = $db->prepare(
    'INSERT INTO indicador_variables (indicador_id, nombre_variable, descripcion) VALUES (?, ?, ?)'
);

$db->beginTransaction();

try {
    // Evita choques con la clave única de código mientras se renumera.
    $db->exec("UPDATE indicadores SET codigo = CONCAT('TMP-', id) WHERE codigo LIKE 'ISTS-%'");

    $faltantes = [];
    $criteriosHechos = [];
    $subcriteriosHechos = [];

    foreach (modeloCacesIndicadores() as $numero => [$numeracion, $nombre, $tipo, $formula, $estandar, $variables]) {
        $nuevoCodigo = modeloCacesCodigo($numero);
        $buscar->execute([
            'AE2025-' . str_pad((string) $numero, 2, '0', STR_PAD_LEFT),
            $numeracion . ' %',
        ]);
        $fila = $buscar->fetch();

        if (!$fila) {
            $faltantes[] = "{$nuevoCodigo} {$numeracion} {$nombre}";
            continue;
        }

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

    $restantes = (int) $db->query("SELECT COUNT(*) FROM indicadores WHERE codigo LIKE 'TMP-%'")->fetchColumn();
    if ($restantes > 0) {
        throw new RuntimeException("Quedaron {$restantes} indicadores sin código asignado.");
    }

    echo "\nIndicadores actualizados: " . (43 - count($faltantes)) . "\n";
    echo 'Subcriterios actualizados: ' . count($subcriteriosHechos) . "\n";
    echo 'Criterios actualizados: ' . count($criteriosHechos) . "\n";
    if ($faltantes) {
        echo "No encontrados en la base de datos:\n  - " . implode("\n  - ", $faltantes) . "\n";
    }

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
