<?php require BASE_PATH . '/app/views/layout/header.php'; ?>

<?php
require_once BASE_PATH . '/app/models/IndicadorModel.php';

$indicadorModel = new IndicadorModel();

$indicador = $indicadorModel->getById($evaluacion['indicador_id']);

$variables = [];
if ($indicador['tipo'] === 'cuantitativo') {
    $variables = $indicadorModel->getVariables($indicador['id']);

    // Si no hay variables en la tabla, extraer de la fórmula
    if (empty($variables) && !empty($indicador['formula'])) {
        preg_match_all('/\b[A-Z]+\b/', $indicador['formula'], $matches);
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

        <div class="formula-box mb-4">
            <strong>Fórmula del Indicador:</strong><br>
            <?= htmlspecialchars($indicador['formula']) ?>
        </div>

        <?php if ($indicador['tipo'] === 'cuantitativo'): ?>

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

        <?php endif; ?>

        <?php if ($evaluacion['valor_calculado']): ?>

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

        <?php endif; ?>
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
        rows="3"><?= htmlspecialchars($evaluacion['observaciones']) ?></textarea>
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

        const formula = "<?= addslashes($indicador['formula']) ?>";
        const estandar = <?= $indicador['valor_estandar'] ?>;

        const inputs = document.querySelectorAll("input[name^='variables']");

        inputs.forEach(input => {
            input.addEventListener("input", calcular);
        });

        function calcular() {

            let expr = formula;

            inputs.forEach(input => {
                let nombre = input.name.match(/\[(.*?)\]/)[1];
                let valor = parseFloat(input.value) || 0;
                expr = expr.replaceAll(nombre, valor);
            });

            try {

                let resultado = eval(expr);
                let porcentajeNum = (resultado / estandar) * 100;
                porcentajeNum = Math.min(100, porcentajeNum);
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
                console.log("Error en fórmula");
            }
        }

        calcular();
    });
</script>

<?php require BASE_PATH . '/app/views/layout/footer.php'; ?>