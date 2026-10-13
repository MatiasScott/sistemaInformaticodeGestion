<?php require BASE_PATH . '/app/views/layout/header.php'; ?>

<?php
require_once BASE_PATH . '/app/models/IndicadorModel.php';

$indicadorModel = new IndicadorModel();

$indicador = $indicadorModel->getById($evaluacion['indicador_id']);
$formulaIndicador = trim((string) ($indicador['formula'] ?? ''));

$variables = [];
if ($indicador['tipo'] === 'cuantitativo') {
    $variables = $indicadorModel->getVariables($indicador['id']);

    // Si no hay variables en la tabla, extraer de la fórmula
    if (empty($variables) && $formulaIndicador !== '') {
        preg_match_all('/\b[A-Z]+\b/', $formulaIndicador, $matches);
        $vars_from_formula = array_unique($matches[0]);
        foreach ($vars_from_formula as $var) {
            $variables[] = ['nombre_variable' => $var];
        }
    }
}

$valores_previos = [];

if (!empty($evaluacion['valor_ingresado'])) {
    $valores_previos = json_decode($evaluacion['valor_ingresado'], true);
}
?>

<div class="card">

    <h2>
        <i class="fas fa-edit"></i> Editar Evaluación
    </h2>
    <hr class="mb-3">

    <div class="edit-alert">
        <i class="fas fa-info-circle"></i>
        Está modificando una evaluación ya registrada.
        Verifique los valores antes de guardar los cambios.
    </div>

    <form method="POST">

        <!-- ===================== -->
        <h3 class="section-title">Información General</h3>

        <div class="d-flex mb-4">
            <div class="flex-1 form-group">
                <label>Periodo:</label>
                <input type="text"
                    value="<?= htmlspecialchars($evaluacion['periodo_nombre'] ?? 'Sin nombre') ?>"
                    readonly
                    class="bg-light">
                <input type="hidden"
                    name="periodo_id"
                    value="<?= $evaluacion['periodo_id'] ?>">
            </div>

            <div class="flex-1 form-group">
                <label>Indicador:</label>
                <input type="text"
                    value="<?= htmlspecialchars($evaluacion['indicador_nombre'] ?? 'Sin nombre') ?>"
                    readonly
                    class="bg-light">
                <input type="hidden"
                    name="indicador_id"
                    value="<?= $evaluacion['indicador_id'] ?>">
            </div>
        </div>

        <!-- ===================== -->

        <?php if ($indicador['tipo'] === 'cuantitativo'): ?>

            <div class="formula-box mb-4">
                <strong>Fórmula del Indicador:</strong><br>
                <?= htmlspecialchars($formulaIndicador !== '' ? $formulaIndicador : 'No hay una fórmula configurada para este indicador.') ?>
            </div>

            <h3 class="section-title">Variables del Indicador</h3>

            <?php foreach ($variables as $v): ?>
                <div class="form-group mb-3">
                    <label><?= $v['nombre_variable'] ?></label>
                    <input type="number"
                        step="0.01"
                        name="variables[<?= $v['nombre_variable'] ?>]"
                        value="<?= $valores_previos[$v['nombre_variable']] ?? '' ?>"
                        required>
                </div>
            <?php endforeach; ?>
        <?php else: ?>

            <?php $escalaSeleccionada = $valores_previos['escala_id'] ?? ''; ?>
            <h3 class="section-title">Escala Cualitativa</h3>
            <div class="scale-info">
                Seleccione la categoría que describe el resultado. El porcentaje es el valor de la
                escala × 100: 0 = 0 %, 0.35 = 35 %, 0.70 = 70 % y 1 = 100 %.
                La diferencia se calcula respecto a 1 (cumplimiento completo), no respecto al estándar del indicador.
            </div>
            <div class="form-group mb-3">
                <label for="escala_cualitativa_id">Resultado:</label>
                <select id="escala_cualitativa_id" name="escala_cualitativa_id" required>
                    <option value="">Seleccione una escala activa</option>
                    <?php foreach (($escalasCualitativas ?? []) as $escala): ?>
                        <option value="<?= (int) $escala['id'] ?>"
                            data-valor="<?= htmlspecialchars($escala['valor']) ?>"
                            <?= (string) $escalaSeleccionada === (string) $escala['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($escala['nombre']) ?> (<?= htmlspecialchars($escala['valor']) ?> = <?= number_format((float) $escala['valor'] * 100, 2) ?> %)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

        <?php endif; ?>

            <hr>

            <h3 class="section-title">Resultado (Automático)</h3>
            <div class="scale-info">
                <i class="fas fa-calculator"></i>
                Estos valores se calculan automáticamente y no pueden ser modificados manualmente.
            </div>

            <div class="d-flex mb-4">

                <div class="flex-1 form-group">
                    <label>Valor Calculado</label>
                    <input id="valor_calculado" type="text"
                        value="<?= $evaluacion['valor_calculado'] ?? '' ?>"
                        readonly
                        class="bg-light">
                </div>

                <div class="flex-1 form-group">
                    <label>Porcentaje Obtenido</label>
                    <input id="porcentaje_obtenido" type="text"
                        value="<?= isset($evaluacion['porcentaje_obtenido']) ? $evaluacion['porcentaje_obtenido'] . ' %' : '' ?>"
                        readonly
                        class="bg-light">
                </div>

            </div>

            <div class="form-group mb-4">
                <label>Diferencia</label>
                    <?php $difInicial = (float)($evaluacion['diferencia'] ?? 0); ?>
                <input id="diferencia" type="text"
                    value="<?= ($difInicial > 0 ? '+' : '') . number_format($difInicial, 2, '.', '') ?>"
                    readonly
                    class="bg-light">
            </div>

<!-- ===================== -->
<h3 class="section-title">Estado y Observaciones</h3>

<div class="form-group mb-4">
    <label>Estado (Automático)</label>
    <input id="estado_auto" type="text"
        value="<?= ucfirst($evaluacion['estado']) ?>"
        readonly
        class="bg-light">
</div>

<input type="hidden" name="estado" value="<?= $evaluacion['estado'] ?>">

<div class="form-group mb-4">
    <label for="observaciones">Observaciones:</label>
    <textarea name="observaciones"
        id="observaciones"
        rows="3"><?= htmlspecialchars($evaluacion['observaciones'] ?? '') ?></textarea>
</div>

<!-- ===================== -->
<div class="btn-group mt-3">
    <button type="submit" class="btn btn-primary">
        <i class="fas fa-save"></i> Actualizar Evaluación
    </button>

    <a href="<?= URL_PATH ?>evaluaciones" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> Volver
    </a>
</div>

</form>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function() {

        const formula = <?= json_encode($formulaIndicador) ?>;
        const cualitativo = <?= json_encode($indicador['tipo'] === 'cualitativo') ?>;
        const estandar = cualitativo ? 1 : <?= json_encode((float) ($indicador['valor_estandar'] ?? 0)) ?>;
        const decimales = <?= json_encode((int) ($indicador['decimales'] ?? 0)) ?>;
        const escalaSelect = document.getElementById("escala_cualitativa_id");
        const mensaje = document.createElement("p");
        mensaje.className = "alert alert-danger";
        mensaje.hidden = true;
        document.getElementById("valor_calculado").parentElement.appendChild(mensaje);

        const inputs = document.querySelectorAll("input[name^='variables']");

        inputs.forEach(input => {
            input.addEventListener("input", calcular);
        });
        if (escalaSelect) {
            escalaSelect.addEventListener("change", calcular);
        }

        function limpiar(error = "") {
            ["valor_calculado", "porcentaje_obtenido", "diferencia"].forEach(id => {
                document.getElementById(id).value = "";
            });
            document.getElementById("estado_auto").value = "pendiente";
            document.querySelector('input[name="estado"]').value = "pendiente";
            mensaje.textContent = error;
            mensaje.hidden = !error;
        }

        function calcular() {
            mensaje.hidden = true;
            try {
                let resultado;
                if (cualitativo) {
                    if (!escalaSelect.value) {
                        limpiar();
                        return;
                    }
                    resultado = Number(escalaSelect.selectedOptions[0].dataset.valor);
                    if (!Number.isFinite(resultado) || resultado < 0 || resultado > 1) {
                        throw new Error("La escala debe tener un valor entre 0 y 1.");
                    }
                } else {
                    if (!formula || estandar <= 0) {
                        throw new Error("Configure una fórmula y un valor estándar mayor que cero.");
                    }
                    if (Array.from(inputs).some(input => input.value.trim() === "")) {
                        limpiar();
                        return;
                    }
                    const valores = new Map(Array.from(inputs, input => [
                        input.name.match(/\[(.*?)\]/)[1], Number(input.value)
                    ]));
                    const expr = formula.replace(/\b[A-Z]+\b/g, nombre => {
                        const valor = valores.get(nombre);
                        if (!Number.isFinite(valor)) {
                            throw new Error(`Ingrese un valor válido para ${nombre}.`);
                        }
                        return String(valor);
                    });
                    if (!/^[\d\s.+*/()%eE-]+$/.test(expr)) {
                        throw new Error("La fórmula contiene una expresión no válida.");
                    }
                    resultado = eval(expr);
                    if (!Number.isFinite(resultado)) {
                        throw new Error("La fórmula no produce un resultado válido. Revise los valores y las divisiones por cero.");
                    }
                    const factor = 10 ** decimales;
                    resultado = Math.sign(resultado) * Math.round(Math.abs(resultado) * factor) / factor;
                }
                let porcentajeNum = Math.min(100, Math.round((resultado / estandar) * 10000) / 100);
                let diferencia = resultado - estandar;

                let resultadoFmt = resultado.toFixed(2);
                let porcentajeFmt = porcentajeNum.toFixed(2);

                let estado = 'rechazado';
                if (porcentajeNum >= 100) {
                    estado = 'aprobado';
                } else if (porcentajeNum >= 80) {
                    estado = 'pendiente';
                }

                document.getElementById("valor_calculado").value = resultadoFmt;
                document.getElementById("porcentaje_obtenido").value = porcentajeFmt + " %";
                const diferenciaFmt = (diferencia > 0 ? '+' : '') + diferencia.toFixed(2);
                document.getElementById("diferencia").value = diferenciaFmt;
                document.getElementById("estado_auto").value = estado;

                const estadoHidden = document.querySelector('input[name="estado"]');
                if (estadoHidden) {
                    estadoHidden.value = estado;
                }

            } catch (e) {
                limpiar(e.message || "No se pudo calcular el resultado.");
            }
        }

        calcular();
    });
</script>

<?php require BASE_PATH . '/app/views/layout/footer.php'; ?>