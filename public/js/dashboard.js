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

    function createHorizontalBarChart(id, labels, data, tooltipTitles = null, maxX = null) {

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
                layout: { padding: { right: 40 } },
                scales: maxX ? { x: { min: 0, max: maxX } } : {},
                plugins: {
                    legend: { display: false },
                    tooltip: tooltipTitles ? {
                        callbacks: {
                            title: items => items.length ? (tooltipTitles[items[0].dataIndex] || items[0].label) : ''
                        }
                    } : {},
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

    const nombreCorto = (nombre, max = 40) => {
        const texto = String(nombre ?? 'Sin nombre');
        return texto.length > max ? texto.substring(0, max) + '...' : texto;
    };

    [
        ['avanceProyectos', avanceProyectos],
        ['avanceProyectosVinculacion', avanceProyectosVinculacion],
        ['avanceProyectosInvestigacion', avanceProyectosInvestigacion]
    ].forEach(([id, proyectos]) => createHorizontalBarChart(
        id,
        proyectos.map(p => nombreCorto(p.nombre_proyecto)),
        proyectos.map(p => parseFloat(p.porcentaje_avance) || 0),
        proyectos.map(p => p.nombre_proyecto || 'Sin nombre'),
        100
    ));

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
        pediData.map(p => parseFloat(p.avance) || 0),
        pediData.map(p => p.objetivo_nombre || ''),
        100
    );

    /* ==============================
    POA
    ============================== */

    const ctxPoa = getCtx('chartPoa');

    if (ctxPoa && poaData.length > 0) {

        const poaPorcentajes = poaData.map(p => {

            const asignado = parseFloat(p.asignado) || 0;
            const ejecutado = parseFloat(p.ejecutado) || 0;

            return {
                area: p.nombre_area,
                asignado: asignado,
                ejecutado: asignado ? (ejecutado / asignado * 100) : 0,
                por_ejecutar: asignado ? ((asignado - ejecutado) / asignado * 100) : 0
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
                        label: 'Por ejecutar (%)',
                        data: poaPorcentajes.map(p => p.por_ejecutar),
                        backgroundColor: '#ef4444'
                    }

                ]

            },

            options: {
                indexAxis: 'y',
                layout: { padding: { right: 40 } },
                scales: { x: { min: 0, max: 100 } },

                plugins: {

                    tooltip: {
                        callbacks: {
                            label: c => `${c.dataset.label}: ${c.parsed.x.toFixed(2)}%`,
                            footer: items => items.length
                                ? 'Presupuesto asignado: $' + poaPorcentajes[items[0].dataIndex].asignado.toLocaleString('es-EC', { minimumFractionDigits: 2 })
                                : ''
                        }
                    },

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
                layout: { padding: { right: 40 } },
                scales: { x: { min: 0, max: 100 } },
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

    const ctxCriterio = getCtx('criterioChart');

    if (ctxCriterio && byCriterio.length > 0) {

        const formatoDosDecimales = v => (v != null && !isNaN(Number(v))) ? Number(v).toFixed(2) + '%' : '';
        const coloresAnio = ['#3b82f6', '#22c55e', '#f59e0b', '#a855f7', '#ef4444', '#14b8a6'];

        const criterios = [...new Set(byCriterio.map(i => i.criterio))];
        const anios = [...new Set(byCriterio.map(i => Number(i.anio)))].sort((a, b) => a - b);
        const buscar = (criterio, anio) => byCriterio.find(i => i.criterio === criterio && Number(i.anio) === anio) || {};

        // Una barra por año dentro de cada criterio: aporte al resultado anual (avance × peso / 100).
        new Chart(ctxCriterio, {
            type: 'bar',
            data: {
                labels: criterios,
                datasets: anios.map((anio, idx) => ({
                    label: String(anio),
                    data: criterios.map(c => {
                        const aporte = buscar(c, anio).aporte;
                        return aporte != null ? Number(aporte) : null;
                    }),
                    backgroundColor: coloresAnio[idx % coloresAnio.length],
                    borderRadius: 4
                }))
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true,
                        grace: '10%',
                        ticks: { callback: value => Number(value).toFixed(2) + '%' }
                    }
                },
                plugins: {
                    legend: { display: true, position: 'top' },
                    datalabels: { formatter: formatoDosDecimales },
                    tooltip: {
                        callbacks: {
                            title: items => items.length ? `${items[0].label} (${items[0].dataset.label})` : '',
                            label: context => {
                                const item = buscar(context.label, Number(context.dataset.label));
                                return [
                                    'Aporte al total: ' + formatoDosDecimales(item.aporte),
                                    'Peso: ' + formatoDosDecimales(item.peso),
                                    'Avance: ' + formatoDosDecimales(item.avance)
                                ];
                            }
                        }
                    }
                }
            }
        });

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
                            formatter: v => (v != null && !isNaN(v)) ? v.toFixed(2) + '%' : ''
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