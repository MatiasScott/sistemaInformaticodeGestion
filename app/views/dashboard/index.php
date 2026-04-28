<?php
/**
 * Variables inyectadas vía extract($data) desde DashboardController
 *
 * @var array       $user
 * @var string      $ultimoPeriodo
 * @var int         $totalEstudiantes
 * @var int         $totalProfesoresTP
 * @var int         $totalProfesoresMT
 * @var int         $practicasActivas
 * @var int         $planesActivos
 * @var int         $documentosPendientes
 * @var int         $bloqueados
 * @var int         $totalGraduados
 * @var int         $totalCarreras
 * @var int         $totalProfesores
 * @var int         $totalProfesoresTC
 * @var int         $totalProyectos
 * @var int         $totalProyectosInvestigacion
 * @var int         $totalProyectosVinculacion
 * @var int|float   $totalBeneficiarios
 * @var int         $totalEstudiantesProyectos
 * @var int         $totalPonencias
 * @var int         $totalPublicaciones
 * @var float       $promedioAvanceProyectos
 * @var array       $estudiantesPrograma
 * @var array       $graduadosCarreras
 * @var array       $modalidades
 * @var array       $proyectosTipo
 * @var array       $AvanceProyectos
 * @var array       $AvanceProyectosInvestigacion
 * @var array       $AvanceProyectosVinculacion
 * @var array       $areas
 * @var array       $institutional
 * @var array       $byIndicator
 * @var array       $byCriterio
 * @var int         $startYear
 * @var int         $endYear
 */
?>
<?php require BASE_PATH . '/app/views/layout/header.php'; ?>

<div class="dashboard-header">
    <div>
        <h2>Panel Institucional</h2>
        <p><?= date('d/m/Y H:i') ?></p>
    </div>
    <div>
        <strong><?= $user['primer_nombre'] ?> <?= $user['primer_apellido'] ?></strong>
    </div>
</div>

<!-- ========================================================= -->
<!-- ===================== KPIs GLOBALES ===================== -->
<!-- ========================================================= -->

<div class="dashboard-section">
    <h3>KPIs Globales - <?= $ultimoPeriodo ?></h3>

    <div class="kpi-grid">

        <div class="kpi-card info">
            <h3><?= $totalEstudiantes ?></h3>
            <p>Estudiantes Matriculados (Periodo Actual)</p>
        </div>

        <div class="kpi-card warning">
            <h3><?= $totalProfesoresTP ?? 0 ?></h3>
            <p>Profesores Tiempo Parcial (TP) (Periodo Actual)</p>
        </div>

        <div class="kpi-card success">
            <h3><?= $totalProfesoresTC ?? 0 ?></h3>
            <p>Profesores Tiempo Completo (TC) (Periodo Actual)</p>
        </div>

        <div class="kpi-card dark">
            <h3><?= $totalProfesoresMT ?? 0 ?></h3>
            <p>Profesores Medio Tiempo (MT) (Periodo Actual)</p>
        </div>

        <div class="kpi-card primary">
            <h3><?= $practicasActivas ?></h3>
            <p>Estudiantes en Prácticas Preprofesionales</p>
        </div>

        <div class="kpi-card dark">
            <h3><?= $totalProyectosInvestigacion ?? 0 ?></h3>
            <p>Total Proyectos Investigación (Activos)</p>
        </div>

        <div class="kpi-card info">
            <h3><?= $totalProyectosVinculacion ?? 0 ?></h3>
            <p>Total Proyectos Vinculación (Activos)</p>
        </div>

        <div class="kpi-card warning">
            <h3><?= $totalBecados ?? 0 ?></h3>
            <p>Estudiantes con Beca</p>
        </div>

    </div>
</div>

<!-- ========================================================= -->
<!-- ===================== EJE INSTITUCIONAL ================= -->
<!-- ========================================================= -->

<div class="dashboard-section">

    <h3>Eje Institucional</h3>

    <div class="chart-grid mt-3">

        <div class="card">
            <h4>Avance PEDI</h4>
            <canvas id="chartPedi"></canvas>
        </div>

    </div>

    <div class="chart-grid mt-3">

        <div class="card">
            <h4>Presupuesto por Área</h4>
            <canvas id="chartPoa"></canvas>
        </div>

    </div>

    <div class="chart-grid mt-3">

        <div class="card">
            <h4>Avance Actividades</h4>

            <div class="mb-4 flex items-center gap-3">

                <label class="text-sm font-medium text-gray-700">
                    Filtrar por área
                </label>

                <select id="filtroArea"
                    class="border rounded-lg px-3 py-1">

                    <option value="todas">Todas</option>

                    <?php foreach ($areas as $area): ?>
                        <option value="<?= $area['nombre_area'] ?>">
                            <?= $area['nombre_area'] ?>
                        </option>
                    <?php endforeach; ?>

                </select>

            </div>

            <canvas id="chartActividades"></canvas>
        </div>

    </div>

</div>

<!-- ========================================================= -->
<!-- ================= EJE GESTIÓN ACADÉMICA ================= -->
<!-- ========================================================= -->

<div class="dashboard-section">
    <h3>Eje Gestión Académica</h3>

    <div class="kpi-grid">

        <div class="kpi-card success">
            <h3>85%</h3>
            <p>Tasa de Retención (Quemado)</p>
        </div>

        <div class="kpi-card danger">
            <h3>12%</h3>
            <p>Tasa de Deserción (Quemado)</p>
        </div>

        <div class="kpi-card success">
            <h3>75%</h3>
            <p>Tasa de Titulación (Quemado)</p>
        </div>

        <div class="kpi-card info">
            <h3>50%</h3>
            <p>Tasa de Empleabilidad (Quemado)</p>
        </div>

        <div class="kpi-card warning">
            <h3>15%</h3>
            <p>Tasa de Recuperación (Quemado)</p>
        </div>

        <div class="kpi-card primary">
            <h3>85%</h3>
            <p>Uso de la Biblioteca Fisica (Quemado)</p>
        </div>

        <div class="kpi-card dark">
            <h3>75%</h3>
            <p>Uso de la Biblioteca Virtual (Quemado)</p>
        </div>

        <div class="kpi-card info">
            <h3><?= $totalCarreras ?? 0 ?></h3>
            <p>Carreras Activas</p>
        </div>

        <div class="kpi-card success">
            <h3><?= $totalGraduados ?? 0 ?></h3>
            <p>Estudiantes Graduados</p>
        </div>

        <div class="kpi-card primary">
            <h3>12</h3>
            <p>Número de Cohortes de graduados</p>
        </div>

    </div>

    <div class="card mt-3">
        <h4>Estudiantes por Carrera</h4>
        <canvas id="estudiantesPrograma"></canvas>
    </div>

    <div class="card mt-3">
        <h4>Estudiantes Graduados por Carrera (Historico)</h4>
        <canvas id="graduadosCarreras"></canvas>
    </div>

</div>

<!-- ========================================================= -->
<!-- ===== EJE INVESTIGACIÓN, INNOVACIÓN Y VINCULACIÓN ====== -->
<!-- ========================================================= -->

<div class="dashboard-section">
    <h3>Eje Investigación, Desarrollo, Innovación y Vinculación con la sociedad</h3>

    <div class="kpi-grid">

        <div class="kpi-card dark">
            <h3><?= $totalProyectos ?? 0 ?></h3>
            <p>Total Proyectos</p>
        </div>

        <div class="kpi-card info">
            <h3><?= $totalEstudiantesProyectos ?? 0 ?></h3>
            <p>Estudiantes en Proyectos</p>
        </div>

        <div class="kpi-card success">
            <h3><?= $totalBeneficiarios ?? 0 ?></h3>
            <p>Beneficiarios de Proyectos</p>
        </div>

        <div class="kpi-card warning">
            <h3><?= $totalPonencias ?? 0 ?></h3>
            <p>Eventos Comunidad</p>
        </div>

        <div class="kpi-card dark">
            <h3><?= $totalConvenios ?? 0 ?></h3>
            <p>Total Convenios</p>
        </div>

        <div class="kpi-card success">
            <h3><?= $conveniosVigentes ?? 0 ?></h3>
            <p>Convenios Vigentes</p>
        </div>

        <div class="kpi-card info">
            <h3><?= $conveniosEjecucion ?? 0 ?></h3>
            <p>Convenios en Ejecución</p>
        </div>

    </div>

    <div class="chart-grid mt-3">
        <div class="card">
            <h4>Estudiantes por Modalidad de Práctica</h4>
            <canvas id="modalidadesPractica"></canvas>
        </div>

        <div class="card">
            <h4>Avance de Proyectos de Vinculación</h4>
            <canvas id="avanceProyectosVinculacion"></canvas>
        </div>
    </div>

    <div class="chart-grid mt-3">

        <div class="card">
            <h4>Avance de Proyectos de Investigación</h4>
            <canvas id="avanceProyectosInvestigacion"></canvas>
        </div>
    </div>

    <div class="chart-grid mt-3">

        <div class="card">
            <h4>Estado de Convenios</h4>
            <canvas id="chartEstadoConvenio"></canvas>
        </div>

        <div class="card">
            <h4>Tipo de Convenio</h4>
            <canvas id="chartTipoConvenio"></canvas>
        </div>

        <div class="card">
            <h4>Tipo de Institución</h4>
            <canvas id="chartTipoInstitucion"></canvas>
        </div>

        <div class="card">
            <h4>Convenios en Ejecución</h4>
            <canvas id="chartEjecucion"></canvas>
        </div>

    </div>

    <div class="chart-grid mt-3">

        <div class="card">
            <h4>Convenios por Carrera</h4>
            <canvas id="chartCarrerasConvenio"></canvas>
        </div>

        <div class="card">
            <h4>Convenios por Ciudad</h4>
            <canvas id="chartCiudadConvenio"></canvas>
        </div>

    </div>
</div>

<!-- ========================================================= -->
<!-- ========= RESULTADOS AUTOEVALUACIÓN INSTITUCIONAL ====== -->
<!-- ========================================================= -->

<div class="dashboard-section">
    <h3>Resultados de Autoevaluación Institucional (<?= $startYear ?> - <?= $endYear ?>)</h3>

    <div class="card">
        <canvas id="institutionalChart"></canvas>
    </div>

    <div class="card mt-3">
        <h4>Resultados por Criterio</h4>
        <canvas id="criterioChart"></canvas>
    </div>
</div>

<script>
    window.dashboardData = {
        modalidades: <?= json_encode($modalidades ?? []) ?>,
        institutional: <?= json_encode($institutional ?? []) ?>,
        byIndicator: <?= json_encode($byIndicator ?? []) ?>,
        byCriterio: <?= json_encode($byCriterio ?? []) ?>,
        proyectosTipo: <?= json_encode($proyectosTipo ?? []) ?>,
        estudiantesPrograma: <?= json_encode($estudiantesPrograma ?? []) ?>,
        promedioAvance: <?= json_encode($promedioAvanceProyectos ?? 0) ?>,
        avanceProyectos: <?= json_encode($AvanceProyectos ?? []) ?>,
        avanceProyectosVinculacion: <?= json_encode($AvanceProyectosVinculacion ?? []) ?>,
        avanceProyectosInvestigacion: <?= json_encode($AvanceProyectosInvestigacion ?? []) ?>,
        pediData: <?= json_encode($avancePedi ?? []) ?>,
        poaData: <?= json_encode($avancePoa ?? []) ?>,
        actividadesData: <?= json_encode($actividadesPoa ?? []) ?>,
        estadoConvenio: <?= json_encode($kpiEstadoConvenio ?? []) ?>,
        tipoConvenio: <?= json_encode($kpiTipoConvenio ?? []) ?>,
        tipoInstitucion: <?= json_encode($kpiTipoInstitucion ?? []) ?>,
        ejecucionConvenio: <?= json_encode($kpiEnEjecucion ?? []) ?>,
        carrerasConvenio: <?= json_encode($graficoCarreras ?? []) ?>,
        ciudadConvenio: <?= json_encode($graficoCiudad ?? []) ?>,
        graduadosCarreras: <?= json_encode($graduadosCarreras ?? []) ?>
    };
</script>

<script src="<?= URL_PATH ?>js/dashboard.js"></script>

<?php require BASE_PATH . '/app/views/layout/footer.php'; ?>