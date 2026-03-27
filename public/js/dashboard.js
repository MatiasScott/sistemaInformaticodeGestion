document.addEventListener("DOMContentLoaded", function () {

    Chart.register(ChartDataLabels);

    Chart.defaults.plugins.datalabels = {
        color: '#000',
        anchor: 'end',
        align: 'top',
        font: {
            weight: 'bold',
            size: 11
        },
        formatter: (value) => (value != null && !isNaN(Number(value))) ? value : ''
    };

    /* ==============================
    DATOS
    ============================== */

    const proyectosTipo = dashboardData.proyectosTipo || [];
    const modalidades = dashboardData.modalidades || [];
    const institutional = dashboardData.institutional || [];
    const byIndicator = dashboardData.byIndicator || [];
    const byCriterio = dashboardData.byCriterio || [];
    const estudiantesPrograma = dashboardData.estudiantesPrograma || [];
    const promedioAvance = dashboardData.promedioAvance || 0;
    const avanceProyectos = dashboardData.avanceProyectos || [];
    const avanceProyectosVinculacion = dashboardData.avanceProyectosVinculacion || [];
    const avanceProyectosInvestigacion = dashboardData.avanceProyectosInvestigacion || [];
    const pediData = dashboardData.pediData || [];
    const poaData = dashboardData.poaData || [];
    const actividadesData = dashboardData.actividadesData || [];
    const estadoConvenio = dashboardData.estadoConvenio || [];
    const tipoConvenio = dashboardData.tipoConvenio || [];
    const tipoInstitucion = dashboardData.tipoInstitucion || [];
    const ejecucionConvenio = dashboardData.ejecucionConvenio || [];
    const carrerasConvenio = dashboardData.carrerasConvenio || [];
    const ciudadConvenio = dashboardData.ciudadConvenio || [];
    const graduadosCarreras = dashboardData.graduadosCarreras || [];

    /* ==============================
    FUNCIONES GENERALES
    ============================== */

    function getCtx(id) {
        const ctx = document.getElementById(id);
        if (!ctx) return null;
        return ctx;
    }

    function createBarChart(id, labels, data, options = {}) {

        const ctx = getCtx(id);
        if (!ctx) return;

        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels.map(l => l ?? ''),
                datasets: [{
                    label: '',
                    data: data.map(v => (v != null && !isNaN(Number(v))) ? Number(v) : 0),
                    backgroundColor: 'rgba(54,162,235,0.6)'
                }]
            },
            options: Object.assign({ plugins: { legend: { display: false } } }, options)
        });
    }

    function createHorizontalBarChart(id, labels, data) {

        const ctx = getCtx(id);
        if (!ctx) return;

        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels.map(l => l ?? ''),
                datasets: [{
                    label: '',
                    data: data.map(v => (v != null && !isNaN(Number(v))) ? Number(v) : 0),
                    backgroundColor: 'rgba(54,162,235,0.6)'
                }]
            },
            options: {
                indexAxis: 'y',
                plugins: {
                    legend: { display: false },
                    datalabels: {
                        align: 'right',
                        anchor: 'end',
                        formatter: (value) => (value != null && !isNaN(Number(value))) ? Number(value).toLocaleString() : ''
                    }
                }
            }
        });
    }

    function createPieChart(id, labels, data, type = 'pie') {

        const ctx = getCtx(id);
        if (!ctx) return;

        new Chart(ctx, {
            type: type,
            data: {
                labels: labels,
                datasets: [{
                    data: data
                }]
            },
            options: {

                plugins: {

                    legend: {
                        position: 'bottom'
                    },

                    datalabels: {

                        anchor: 'center',
                        align: 'center',

                        color: '#000',

                        font: {
                            weight: 'bold',
                            size: 14
                        },

                        formatter: (value) => value
                    }

                }

            }
        });
    }

    /* ==============================
    AVANCE PROYECTOS
    ============================== */

    createHorizontalBarChart(
        'avanceProyectos',
        avanceProyectos.map(p => p.nombre_proyecto.substring(0, 40) + '...'),
        avanceProyectos.map(p => parseFloat(p.porcentaje_avance) || 0)
    );

    createHorizontalBarChart(
        'avanceProyectosVinculacion',
        avanceProyectosVinculacion.map(p => p.nombre_proyecto.substring(0, 40) + '...'),
        avanceProyectosVinculacion.map(p => parseFloat(p.porcentaje_avance) || 0)
    );

    createHorizontalBarChart(
        'avanceProyectosInvestigacion',
        avanceProyectosInvestigacion.map(p => p.nombre_proyecto.substring(0, 40) + '...'),
        avanceProyectosInvestigacion.map(p => parseFloat(p.porcentaje_avance) || 0)
    );

    /* ==============================
    PROYECTOS
    ============================== */

    createPieChart(
        'proyectosTipo',
        proyectosTipo.map(p => p.tipo_proyecto),
        proyectosTipo.map(p => p.total),
        'doughnut'
    );

    createPieChart(
        'modalidadesPractica',
        modalidades.map(m => m.modalidad),
        modalidades.map(m => m.total)
    );

    /* ==============================
    ESTUDIANTES
    ============================== */

    createHorizontalBarChart(
        'estudiantesPrograma',
        estudiantesPrograma.map(e => e.programa),
        estudiantesPrograma.map(e => parseInt(e.total) || 0)
    );

    createHorizontalBarChart(
        'graduadosCarreras',
        graduadosCarreras.map(e => e.programa),
        graduadosCarreras.map(e => parseInt(e.totalgraduado) || 0)
    );

    /* ==============================
    PEDI
    ============================== */

    createHorizontalBarChart(
        'chartPedi',
        pediData.map(p => p.objetivo_estrategia),
        pediData.map(p => parseFloat(p.avance) || 0)
    );

    /* ==============================
    POA
    ============================== */

    const ctxPoa = getCtx('chartPoa');

    if (ctxPoa && poaData.length > 0) {

        const poaPorcentajes = poaData.map(p => {

            const ejecutado = parseFloat(p.ejecutado) || 0;
            const progreso = parseFloat(p.progreso) || 0;
            const no_ejecutado = parseFloat(p.no_ejecutado) || 0;

            const total = ejecutado + progreso + no_ejecutado;

            return {
                area: p.nombre_area,
                ejecutado: total ? (ejecutado / total * 100) : 0,
                progreso: total ? (progreso / total * 100) : 0,
                no_ejecutado: total ? (no_ejecutado / total * 100) : 0
            };

        });

        new Chart(ctxPoa, {

            type: 'bar',

            data: {

                labels: poaPorcentajes.map(p => p.area),

                datasets: [

                    {
                        label: 'Ejecutado (%)',
                        data: poaPorcentajes.map(p => p.ejecutado),
                        backgroundColor: '#22c55e'
                    },

                    {
                        label: 'En progreso (%)',
                        data: poaPorcentajes.map(p => p.progreso),
                        backgroundColor: '#f59e0b'
                    },

                    {
                        label: 'No ejecutado (%)',
                        data: poaPorcentajes.map(p => p.no_ejecutado),
                        backgroundColor: '#ef4444'
                    }

                ]

            },

            options: {
                indexAxis: 'y',

                plugins: {

                    datalabels: {

                        color: '#000',

                        formatter: v => (v != null && !isNaN(v)) ? v.toFixed(1) + '%' : '',

                        anchor: 'end',
                        align: 'right'

                    }

                }

            }

        });

    }

    let chartActividades = null;

    function renderActividades(data) {

        const ctx = getCtx('chartActividades');
        if (!ctx) return;

        if (chartActividades) {
            chartActividades.destroy();
        }

        chartActividades = new Chart(ctx, {

            type: 'bar',

            data: {
                labels: data.map(a => a.nombre_actividad),
                datasets: [{
                    label: 'Avance (%)',
                    data: data.map(a => parseFloat(a.avance) || 0),
                    backgroundColor: 'rgba(54,162,235,0.6)'
                }]
            },

            options: {
                indexAxis: 'y',
                plugins: {
                    datalabels: {
                        align: 'right',
                        anchor: 'end',
                        formatter: v => (v != null && !isNaN(v)) ? v + '%' : ''
                    }
                }
            }

        });

    }

    const filtroArea = document.getElementById('filtroArea');

    if (filtroArea) {

        filtroArea.addEventListener('change', function () {

            const area = this.value;

            let filtrado = actividadesData;

            if (area !== 'todas') {
                filtrado = actividadesData.filter(a => a.nombre_area === area);
            }

            renderActividades(filtrado);

        });

    }

    renderActividades(actividadesData);

    /* ==============================
    CONVENIOS
    ============================== */

    createPieChart(
        'chartEstadoConvenio',
        estadoConvenio.map(e => e.estado_convenio),
        estadoConvenio.map(e => e.total),
        'doughnut'
    );

    createHorizontalBarChart(
        'chartTipoConvenio',
        tipoConvenio.map(t => t.tipo_convenio),
        tipoConvenio.map(t => parseInt(t.total) || 0)
    );

    createPieChart(
        'chartTipoInstitucion',
        tipoInstitucion.map(t => t.tipo_institucion),
        tipoInstitucion.map(t => t.total)
    );

    createPieChart(
        'chartEjecucion',
        ejecucionConvenio.map(e => e.en_ejecucion),
        ejecucionConvenio.map(e => e.total),
        'doughnut'
    );

    createHorizontalBarChart(
        'chartCarrerasConvenio',
        carrerasConvenio.map(c => c.carrera),
        carrerasConvenio.map(c => parseInt(c.total) || 0)
    );

    createBarChart(
        'chartCiudadConvenio',
        ciudadConvenio.map(c => c.ciudad),
        ciudadConvenio.map(c => parseInt(c.total) || 0)
    );

    /* ==============================
    INDICADORES
    ============================== */

    if (byIndicator.length > 0) {

        createBarChart(
            'indicatorChart',
            byIndicator.map(i => i.indicador_nombre),
            byIndicator.map(i => parseFloat(i.resultado))
        );

    }

    if (byCriterio.length > 0) {

        createBarChart(
            'criterioChart',
            byCriterio.map(i => i.criterio),
            byCriterio.map(i => parseFloat(i.avance))
        );

    }

    const ctxInstitutional = getCtx('institutionalChart');

    if (ctxInstitutional && institutional.length > 0) {

        new Chart(ctxInstitutional, {

            data: {

                labels: institutional.map(i => i.year),

                datasets: [

                    {
                        type: 'bar',
                        label: 'Resultado Institucional',
                        data: institutional.map(i => parseFloat(i.resultado)),
                        backgroundColor: 'rgba(54, 162, 235, 0.6)',
                        borderRadius: 6,
                        datalabels: {
                            anchor: 'end',
                            align: 'top',
                            formatter: v => (v != null && !isNaN(v)) ? v.toFixed(1) + '%' : ''
                        }
                    },

                    {
                        type: 'line',
                        label: 'Meta Institucional (90%)',
                        data: institutional.map(() => 90),
                        borderColor: 'rgba(255, 99, 132, 1)',
                        borderWidth: 2,
                        borderDash: [6, 6],
                        fill: false,
                        tension: 0,
                        datalabels: {
                            display: false
                        }
                    },

                    {
                        type: 'line',
                        label: 'Tendencia',
                        data: institutional.map(i => parseFloat(i.resultado)),
                        borderColor: 'rgba(75, 192, 192, 1)',
                        borderWidth: 3,
                        fill: false,
                        tension: 0.4,
                        datalabels: {
                            display: false
                        }
                    }

                ]
            },

            options: {

                responsive: true,

                interaction: {
                    mode: 'index',
                    intersect: false
                },

                scales: {
                    y: {
                        beginAtZero: true,
                        max: 100,
                        ticks: {
                            callback: value => value + '%'
                        }
                    }
                },

                plugins: {
                    legend: {
                        position: 'top'
                    }
                }

            }

        });

    }

});