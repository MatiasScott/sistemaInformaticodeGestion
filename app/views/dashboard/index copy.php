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

<!-- ================= KPIs ================= -->

<div class="kpi-grid">

    <div class="kpi-card primary">
        <h3><?= $totalEstudiantes ?></h3>
        <p>Estudiantes Activos (Periodo Actual)</p>
    </div>

    <div class="kpi-card success">
        <h3><?= $practicasActivas ?></h3>
        <p>Estudiantes registrados en Prácticas</p>
    </div>

    <div class="kpi-card warning">
        <h3><?= $planesActivos ?></h3>
        <p>Actividades del Plan de Mejora</p>
    </div>

    <div class="kpi-card dark">
        <h3><?= $bloqueados ?></h3>
        <p>Estudiantes Bloqueados</p>
    </div>

</div>

<!-- ================= BLOQUE ACADÉMICO ================= -->

<div class="dashboard-section">
    <h3>Gestión Académica</h3>
    <div class="chart-grid">
        <div class="card">
            <h4>Estudiantes por Carrera</h4>
            <canvas id="estudiantesPrograma"></canvas>
        </div>

        <div class="card">
            <h4>Estudiantes por Modalidades de Práctica</h4>
            <div class="chart-container">
                <canvas id="modalidadesPractica"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- ================= BLOQUE CALIDAD ================= -->

<div class="dashboard-section">
    <h3>Gestión de Calidad</h3>
    <div class="chart-grid">
        <div class="card">
            <h4>Planes por Estado</h4>
            <canvas id="planesEstado"></canvas>
        </div>

        <div class="card">
            <h4>Documentos por Estado</h4>
            <canvas id="documentosEstado"></canvas>
        </div>
    </div>
</div>

<!-- ================= BLOQUE INSTITUCIONAL ================= -->

<div class="dashboard-section">
    <h3>Resultados Institucionales</h3>

    <div class="card">
        <h4>Evolución <?= $startYear ?> - <?= $endYear ?></h4>
        <canvas id="institutionalChart"></canvas>
    </div>

    <div class="card mt-3">
        <h4>Resultados por Indicador</h4>
        <canvas id="indicatorChart"></canvas>
    </div>
</div>

<canvas id="hiddenCanvas" style="display:none;"></canvas>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    document.addEventListener("DOMContentLoaded", function() {

        const estudiantesPrograma = <?= json_encode($estudiantesPrograma) ?>;
        const modalidades = <?= json_encode($modalidades) ?>;
        const planesEstado = <?= json_encode($planesEstado) ?>;
        const docsEstado = <?= json_encode($docsEstado) ?>;
        const institutional = <?= json_encode($institutional) ?>;
        const byIndicator = <?= json_encode($byIndicator) ?>;

        // Estudiantes por programa
        const nombreCorto = {
            "TÉCNICO SUPERIOR EN MARKETING DIGITAL": "Marketing Digital",
            "TOPOGRAFÍA CON NIVEL EQUIVALENTE A TECNOLOGIA SUPERIOR": "Topografía",
            "EDUCACIÓN BÁSICA": "Educación Básica",
            "PRODUCCIÓN ANIMAL": "Producción Animal",
            "MINERÍA": "Minería",
            "ENFERMERÍA VETERINARIA": "Enfermería Veterinaria",
            "ADMINISTRACIÓN": "Administración",
            "SEGURIDAD Y PREVENCIÓN DE RIESGOS LABORALES": "Seguridad y Prevención",
            "TECNOLOGÍA SUPERIOR EN PRODUCCIÓN ANIMAL": "Tec. Sup. Producción Animal",
            "TÉCNICO SUPERIOR EN ADMINISTRACIÓN": "Tec. Sup. Administración"
        };


        let programasOrdenados = [...estudiantesPrograma]
            .sort((a, b) => b.total - a.total);

        new Chart(document.getElementById('estudiantesPrograma'), {
            type: 'bar',
            data: {
                labels: programasOrdenados.map(e =>
                    nombreCorto[e.programa?.toUpperCase().trim()] || e.programa
                ),
                datasets: [{
                    label: 'Estudiantes',
                    data: programasOrdenados.map(e => e.total),
                    backgroundColor: 'rgba(54, 162, 235, 0.7)',
                    borderRadius: 6
                }]
            },
            options: {
                indexAxis: 'y', // 👈 horizontal
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    x: {
                        beginAtZero: true
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    }
                }
            }
        });

        // Modalidades
        new Chart(document.getElementById('modalidadesPractica'), {
            type: 'pie',
            data: {
                labels: modalidades.map(m => m.modalidad),
                datasets: [{
                    data: modalidades.map(m => m.total)
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false
            }
        });

        // Planes por estado
        new Chart(document.getElementById('planesEstado'), {
            type: 'doughnut',
            data: {
                labels: planesEstado.map(p => p.estado),
                datasets: [{
                    data: planesEstado.map(p => p.total)
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false
            }
        });

        // Documentos por estado
        new Chart(document.getElementById('documentosEstado'), {
            type: 'doughnut',
            data: {
                labels: docsEstado.map(d => d.estado),
                datasets: [{
                    data: docsEstado.map(d => d.total)
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false
            }
        });

        // Evolución institucional
        new Chart(document.getElementById('institutionalChart'), {
            data: {
                labels: institutional.map(i => i.year),
                datasets: [{
                        type: 'bar',
                        label: 'Resultado Institucional',
                        data: institutional.map(i => parseFloat(i.resultado)),
                        backgroundColor: 'rgba(54, 162, 235, 0.6)',
                        borderRadius: 6
                    },
                    {
                        type: 'line',
                        label: 'Meta Institucional (90%)',
                        data: institutional.map(() => 90),
                        borderColor: 'rgba(255, 99, 132, 1)',
                        borderWidth: 2,
                        borderDash: [6, 6],
                        fill: false,
                        tension: 0
                    },
                    {
                        type: 'line',
                        label: 'Tendencia',
                        data: institutional.map(i => parseFloat(i.resultado)),
                        borderColor: 'rgba(75, 192, 192, 1)',
                        borderWidth: 3,
                        fill: false,
                        tension: 0.4
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    mode: 'index',
                    intersect: false
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 100,
                        ticks: {
                            callback: function(value) {
                                return value + '%';
                            }
                        }
                    }
                },
                plugins: {
                    legend: {
                        position: 'top'
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return context.dataset.label + ': ' + context.parsed.y + '%';
                            }
                        }
                    }
                }
            }
        });

        // Resultados por indicador
        if (byIndicator && byIndicator.length > 0) {
            new Chart(document.getElementById('indicatorChart'), {
                type: 'bar',
                data: {
                    labels: byIndicator.map(i => i.indicador_nombre),
                    datasets: [{
                        label: 'Resultado (%)',
                        data: byIndicator.map(i => parseFloat(i.resultado)),
                        backgroundColor: 'rgba(153, 102, 255, 0.6)'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            max: 100
                        }
                    }
                }
            });
        }

    });
</script>

<?php require BASE_PATH . '/app/views/layout/footer.php'; ?>