<?php

class DashboardModel
{

    private $pdoSig;
    private $pdoCon;
    private $pdoSgpro;

    public function __construct()
    {
        $db = new Database();

        $this->pdoSig = $db->connect("superar1_sig");
        $this->pdoCon = $db->connect("superar1_conectados");
        $this->pdoSgpro = $db->connect("superar1_landing_sgpro");
    }

    private function fetchOne($pdo, $sql)
    {
        return $pdo->query($sql)->fetch(PDO::FETCH_ASSOC);
    }

    private function fetchAll($pdo, $sql)
    {
        return $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    private function fetchAllOptional($pdo, $sql)
    {
        try {
            return $this->fetchAll($pdo, $sql);
        } catch (PDOException $exception) {
            if ($this->isOptionalSchemaException($exception)) {
                return [];
            }

            throw $exception;
        }
    }

    private function isMissingTableException(PDOException $exception)
    {
        $errorInfo = $exception->errorInfo ?? [];
        $sqlState = $errorInfo[0] ?? $exception->getCode();
        $driverCode = $errorInfo[1] ?? null;

        return $sqlState === '42S02' || (int) $driverCode === 1146;
    }

    private function isOptionalSchemaException(PDOException $exception)
    {
        $errorInfo = $exception->errorInfo ?? [];
        $sqlState = $errorInfo[0] ?? $exception->getCode();
        $driverCode = $errorInfo[1] ?? null;

        return $this->isMissingTableException($exception)
            || $sqlState === '42S22'
            || (int) $driverCode === 1054;
    }

    private const MESES = [
        'ENERO' => 1, 'ENE' => 1, 'FEBRERO' => 2, 'FEB' => 2, 'MARZO' => 3, 'MAR' => 3,
        'ABRIL' => 4, 'ABR' => 4, 'MAYO' => 5, 'MAY' => 5, 'JUNIO' => 6, 'JUN' => 6,
        'JULIO' => 7, 'JUL' => 7, 'AGOSTO' => 8, 'AGO' => 8, 'SEPTIEMBRE' => 9, 'SETIEMBRE' => 9,
        'SEP' => 9, 'SEPT' => 9, 'OCTUBRE' => 10, 'OCT' => 10, 'NOVIEMBRE' => 11, 'NOV' => 11,
        'DICIEMBRE' => 12, 'DIC' => 12,
    ];

    private const MESES_ABREVIADOS = [
        1 => 'ENE', 2 => 'FEB', 3 => 'MAR', 4 => 'ABR', 5 => 'MAY', 6 => 'JUN',
        7 => 'JUL', 8 => 'AGO', 9 => 'SEP', 10 => 'OCT', 11 => 'NOV', 12 => 'DIC',
    ];

    /** Meses y años mencionados en el nombre del período, en orden ("PAO MAYO - OCTUBRE 2026" => [5, 10], [2026]). */
    private function partesPeriodo($periodo)
    {
        $texto = strtoupper(strtr((string) $periodo, ['Á' => 'A', 'É' => 'E', 'Í' => 'I', 'Ó' => 'O', 'Ú' => 'U']));
        preg_match_all('/[A-Z]+/', $texto, $palabras);
        preg_match_all('/\b(20\d{2})\b/', $texto, $anios);

        $meses = [];
        foreach ($palabras[0] as $palabra) {
            if (isset(self::MESES[$palabra])) {
                $meses[] = self::MESES[$palabra];
            }
        }

        return [$meses, array_map('intval', $anios[1])];
    }

    /**
     * Período actual = el período de Q10 (users.periodo) que termina más tarde según su nombre
     * (último año y último mes). MAX(periodo) no sirve porque compara texto alfabéticamente.
     */
    private function getDashboardPeriodoActual()
    {
        $periodos = $this->fetchAll($this->pdoCon, "
            SELECT periodo, COUNT(*) total
            FROM users
            WHERE periodo IS NOT NULL AND TRIM(periodo) <> ''
            GROUP BY periodo
        ");

        $actual = null;
        $mejorClave = null;
        foreach ($periodos as $fila) {
            [$meses, $anios] = $this->partesPeriodo($fila['periodo']);
            if (!$anios) {
                continue;
            }
            $clave = [max($anios), $meses ? end($meses) : 0, (int) $fila['total']];
            if ($mejorClave === null || $clave > $mejorClave) {
                $mejorClave = $clave;
                $actual = $fila['periodo'];
            }
        }

        return $actual;
    }

    /**
     * Código con el que Q10 registra la beca del período en estado_beca:
     * "PAO MAYO - OCTUBRE 2026" => "MAY-OCT26", "PAO NOVIEMBRE 2025 - ABRIL 2026" => "NOV25-ABR26".
     */
    private function codigoBecaPeriodo($periodo)
    {
        [$meses, $anios] = $this->partesPeriodo($periodo);
        if (count($meses) < 2 || !$anios) {
            return null;
        }

        $inicio = self::MESES_ABREVIADOS[$meses[0]];
        $fin = self::MESES_ABREVIADOS[end($meses)];
        $anioFin = substr((string) max($anios), -2);

        return count(array_unique($anios)) > 1
            ? $inicio . substr((string) min($anios), -2) . '-' . $fin . $anioFin
            : $inicio . '-' . $fin . $anioFin;
    }

    /**
     * Proyectos activos del período más reciente del tipo indicado. El período se toma del
     * proyecto activo con la fecha de inicio más reciente (no por orden alfabético del nombre).
     */
    private function getAvanceProyectosUltimoPeriodo($tipo)
    {
        if (!in_array($tipo, ['INVESTIGACION', 'VINCULACION'], true)) {
            return [];
        }

        return $this->fetchAll($this->pdoCon, "
            SELECT nombre_proyecto, porcentaje_avance
            FROM proyectos_administracion
            WHERE estado = 'ACTIVO'
            AND tipo_proyecto = '{$tipo}'
            AND periodo_academico = (
                SELECT periodo_academico
                FROM proyectos_administracion
                WHERE estado = 'ACTIVO' AND tipo_proyecto = '{$tipo}'
                ORDER BY fecha_inicio DESC, id_proyecto DESC
                LIMIT 1
            )
            ORDER BY porcentaje_avance DESC
        ");
    }

    /**
     * Años de planificación POA del período actual (p. ej. "PAO NOVIEMBRE 2025 - ABRIL 2026" => 2025, 2026).
     * Si no hay POA en esos años, usa el último año planificado.
     */
    private function getAniosPoa($periodoActual)
    {
        preg_match_all('/\b(20\d{2})\b/', (string) $periodoActual, $matches);
        $anios = array_values(array_unique(array_map('intval', $matches[1])));

        try {
            if ($anios) {
                $existe = $this->fetchOne($this->pdoCon, "
                    SELECT COUNT(*) total FROM poa
                    WHERE estado = 1 AND anio_planificacion IN (" . implode(',', $anios) . ")
                ");
                if ((int) ($existe['total'] ?? 0) > 0) {
                    return $anios;
                }
            }

            $ultimo = $this->fetchOne($this->pdoCon, "SELECT MAX(anio_planificacion) anio FROM poa WHERE estado = 1");
        } catch (PDOException $exception) {
            if ($this->isOptionalSchemaException($exception)) {
                return [];
            }
            throw $exception;
        }

        return !empty($ultimo['anio']) ? [(int) $ultimo['anio']] : [];
    }

    /* =========================
       USUARIO
    ========================= */

    public function getUser($userId)
    {

        $stmt = $this->pdoSig->prepare("
            SELECT id, primer_nombre, primer_apellido
            FROM users
            WHERE id = ?
        ");

        $stmt->execute([$userId]);

        return $stmt->fetch(PDO::FETCH_ASSOC) ?? [
            'primer_nombre' => 'Usuario',
            'primer_apellido' => ''
        ];
    }

    /* =========================
       DASHBOARD DATA
    ========================= */

    public function getDashboardData()
    {

        $data = [];
        $periodoActual = $this->getDashboardPeriodoActual();
        $periodoActualSql = $periodoActual !== null
            ? $this->pdoCon->quote($periodoActual)
            : 'NULL';

        /* =========================
        PERIODO ACTUAL
        ========================= */

        $data['ultimoPeriodo'] = $periodoActual;

        /* =========================
        KPIs
        ========================= */

        $data['totalEstudiantes'] = $this->fetchOne($this->pdoCon, "
            SELECT COUNT(*) total
            FROM users
            WHERE periodo = {$periodoActualSql}
            AND estado = 'Activo'
            AND programa NOT IN ('AUTO EVALUACION','AUTO EVALUCION','SEGUIMIENTO DOCENTE','EJEMPLO 1','EJEMPLO')
        ")['total'];

        $data['practicasActivas'] = $this->fetchOne($this->pdoCon, "
            SELECT COUNT(*) total
            FROM practicas_estudiantes
            WHERE estado = 'ACTIVA'
        ")['total'];

        $data['planesActivos'] = $this->fetchOne($this->pdoSig, "
            SELECT COUNT(*) total
            FROM plan_mejoras
        ")['total'];

        $data['documentosPendientes'] = $this->fetchOne($this->pdoSig, "
            SELECT COUNT(*) total
            FROM documentos
            WHERE estado='pendiente'
        ")['total'];

        $data['bloqueados'] = $this->fetchOne($this->pdoCon, "
            SELECT COUNT(*) total
            FROM pagos_estudiantes
            WHERE ESTADO_BLOQUEO IS NOT NULL
            AND ESTADO_BLOQUEO != ''
        ")['total'];

        /* =========================
        ESTUDIANTES
        ========================= */

        $data['estudiantesPrograma'] = $this->fetchAll($this->pdoCon, "
            SELECT TRIM(programa) programa, COUNT(*) total
            FROM users
            WHERE periodo = {$periodoActualSql}
            AND estado='Activo'AND programa NOT IN ('AUTO EVALUACION','AUTO EVALUCION','SEGUIMIENTO DOCENTE','EJEMPLO 1','EJEMPLO')
            GROUP BY TRIM(programa)
            ORDER BY total DESC
        ");

        $data['graduadosCarreras'] = $this->fetchAll($this->pdoCon, "
            SELECT TRIM(programa) programa, COUNT(*) totalgraduado
            FROM users
            WHERE estado='Graduado'
            GROUP BY TRIM(programa)
        ");

        $data['totalGraduados'] = $this->fetchOne($this->pdoCon, "
            SELECT COUNT(*) totalgraduado
            FROM users
            WHERE estado='Graduado'
        ")['totalgraduado'];

        $data['modalidades'] = $this->fetchAll($this->pdoCon, "
            SELECT modalidad, COUNT(*) total
            FROM practicas_estudiantes
            GROUP BY modalidad
        ");

        $data['totalCarreras'] = $this->fetchOne($this->pdoCon, "
            SELECT COUNT(DISTINCT programa) total
            FROM users
            WHERE periodo = {$periodoActualSql}
        ")['total'];

        /* =========================
        PROYECTOS
        ========================= */

        $data['totalProyectos'] = $this->fetchOne($this->pdoCon, "
            SELECT COUNT(*) total
            FROM proyectos_administracion
            WHERE estado='ACTIVO'
        ")['total'];

        $data['totalProyectosInvestigacion'] = $this->fetchOne($this->pdoCon, "
            SELECT COUNT(*) total
            FROM proyectos_administracion
            WHERE estado='ACTIVO'
            AND tipo_proyecto='INVESTIGACION'
        ")['total'];

        $data['totalProyectosVinculacion'] = $this->fetchOne($this->pdoCon, "
            SELECT COUNT(*) total
            FROM proyectos_administracion
            WHERE estado='ACTIVO'
            AND tipo_proyecto='VINCULACION'
        ")['total'];

        $data['proyectosTipo'] = $this->fetchAll($this->pdoCon, "
            SELECT tipo_proyecto, COUNT(*) total
            FROM proyectos_administracion
            WHERE estado='ACTIVO'
            GROUP BY tipo_proyecto
        ");

        $data['totalBeneficiarios'] = $this->fetchOne($this->pdoCon, "
            SELECT SUM(beneficiarios) total
            FROM proyectos_administracion
            WHERE estado='ACTIVO'
        ")['total'] ?? 0;

        $data['totalEstudiantesProyectos'] = $this->fetchOne($this->pdoCon, "
            SELECT SUM(nro_estudiantes) total
            FROM proyecto_estudiantes_carrera
        ")['total'] ?? 0;

        $data['totalPonencias'] = $this->fetchOne($this->pdoCon, "
            SELECT COUNT(*) total
            FROM ponencias
        ")['total'];

        $data['totalPublicaciones'] = $this->fetchOne($this->pdoCon, "
            SELECT COUNT(*) total
            FROM publicaciones
        ")['total'];

        $data['promedioAvanceProyectos'] = $this->fetchOne($this->pdoCon, "
            SELECT AVG(porcentaje_avance) promedio
            FROM proyectos_administracion
            WHERE estado='ACTIVO'
        ")['promedio'] ?? 0;

        $data['AvanceProyectos'] = $this->fetchAll($this->pdoCon, "
            SELECT nombre_proyecto, porcentaje_avance
            FROM proyectos_administracion
            WHERE estado='ACTIVO'
            ORDER BY porcentaje_avance DESC
        ");

        $data['AvanceProyectosInvestigacion'] = $this->getAvanceProyectosUltimoPeriodo('INVESTIGACION');
        $data['AvanceProyectosVinculacion'] = $this->getAvanceProyectosUltimoPeriodo('VINCULACION');

        /* =========================
        PROFESORES
        ========================= */

        $data['totalProfesores'] = $this->fetchOne($this->pdoSgpro, "
            SELECT COUNT(*) total
            FROM user_roles_pivot
            WHERE role_id = 5
        ")['total'];

        $data['totalProfesoresTP'] = $this->fetchOne($this->pdoSgpro, "
            SELECT COUNT(*) total
            FROM users
            WHERE dedicacion='TIEMPO PARCIAL'
            AND active = 1
        ")['total'];

        $data['totalProfesoresTC'] = $this->fetchOne($this->pdoSgpro, "
            SELECT COUNT(*) total
            FROM users
            WHERE dedicacion='TIEMPO COMPLETO'
            AND active = 1
        ")['total'];

        $data['totalProfesoresMT'] = $this->fetchOne($this->pdoSgpro, "
            SELECT COUNT(*) total
            FROM users
            WHERE dedicacion='MEDIO TIEMPO'
            AND active = 1
        ")['total'];

        /* =========================
        BECAS
        ========================= */

        $codigoBeca = $this->codigoBecaPeriodo($periodoActual);
        $filtroBeca = $codigoBeca !== null
            ? "REPLACE(UPPER(COALESCE(estado_beca, '')), ' ', '') LIKE " . $this->pdoCon->quote('%' . $codigoBeca . '%')
            : "tiene_beca = 'Si'";

        $data['totalBecados'] = $this->fetchOne($this->pdoCon, "
            SELECT COUNT(*) AS total
            FROM users
            WHERE {$filtroBeca}
            AND periodo = {$periodoActualSql}
            AND estado = 'Activo'
        ")['total'];

        /* =========================
        PEDI
        ejes_estrategicos -> objetivos_estrategicos -> estrategias -> poa -> poa_actividades
        ========================= */

        $data['avancePedi'] = $this->fetchAllOptional($this->pdoCon, "
            SELECT
                o.codigo AS objetivo_estrategia,
                o.nombre AS objetivo_nombre,
                ROUND(COALESCE(AVG(a.avance_actividad), o.avance, 0), 2) AS avance,
                COUNT(a.id) AS total_actividades
            FROM objetivos_estrategicos o
            LEFT JOIN estrategias e ON e.objetivo_estrategico_id = o.id AND e.estado = 1
            LEFT JOIN poa p ON p.estrategia_id = e.id AND p.estado = 1
            LEFT JOIN poa_actividades a ON a.poa_id = p.id AND a.estado = 1
            WHERE o.estado = 1
            GROUP BY o.id, o.codigo, o.nombre, o.avance
            ORDER BY o.codigo
        ");

        /* =========================
        POA (área = procesos asociados al POA, igual que en Conectados)
        ========================= */

        $aniosPoa = $this->getAniosPoa($periodoActual);
        $aniosPoaSql = implode(',', $aniosPoa);
        $areaPoaSql = "
            LEFT JOIN (
                SELECT pp.poa_id, GROUP_CONCAT(pr.nombre ORDER BY pr.nombre SEPARATOR ', ') AS nombre_area
                FROM poa_procesos pp
                INNER JOIN procesos pr ON pr.id = pp.proceso_id
                GROUP BY pp.poa_id
            ) area ON area.poa_id = p.id
        ";

        $data['avancePoa'] = $aniosPoa ? $this->fetchAllOptional($this->pdoCon, "
            SELECT
                COALESCE(area.nombre_area, 'Sin área') AS nombre_area,
                SUM(COALESCE(a.presupuesto_asignado, 0)) AS asignado,
                SUM(LEAST(COALESCE(a.presupuesto_ejecutado, 0), COALESCE(a.presupuesto_asignado, 0))) AS ejecutado
            FROM poa p
            INNER JOIN poa_actividades a ON a.poa_id = p.id AND a.estado = 1
            {$areaPoaSql}
            WHERE p.estado = 1
            AND p.anio_planificacion IN ({$aniosPoaSql})
            GROUP BY COALESCE(area.nombre_area, 'Sin área')
            ORDER BY nombre_area
        ") : [];

        $data['actividadesPoa'] = $aniosPoa ? $this->fetchAllOptional($this->pdoCon, "
            SELECT
                COALESCE(area.nombre_area, 'Sin área') AS nombre_area,
                a.nombre AS nombre_actividad,
                COALESCE(a.avance_actividad, 0) AS avance
            FROM poa p
            INNER JOIN poa_actividades a ON a.poa_id = p.id AND a.estado = 1
            {$areaPoaSql}
            WHERE p.estado = 1
            AND p.anio_planificacion IN ({$aniosPoaSql})
            ORDER BY a.avance_actividad DESC
        ") : [];

        $areas = array_values(array_unique(array_column($data['actividadesPoa'], 'nombre_area')));
        sort($areas);
        $data['areas'] = array_map(fn($area) => ['nombre_area' => $area], $areas);

        /* =========================
        CONVENIOS
        ========================= */

        $data['kpiEstadoConvenio'] = $this->fetchAll($this->pdoCon, "
            SELECT estado_convenio, COUNT(*) total
            FROM convenios
            WHERE estado='Activo'
            GROUP BY estado_convenio
        ");

        $data['kpiTipoConvenio'] = $this->fetchAll($this->pdoCon, "
            SELECT tipo_convenio, COUNT(*) total
            FROM convenios
            WHERE estado='Activo'
            GROUP BY tipo_convenio
        ");

        $data['kpiTipoInstitucion'] = $this->fetchAll($this->pdoCon, "
            SELECT tipo_institucion, COUNT(*) total
            FROM convenios
            WHERE estado='Activo'
            GROUP BY tipo_institucion
        ");

        $data['kpiEnEjecucion'] = $this->fetchAll($this->pdoCon, "
            SELECT en_ejecucion, COUNT(*) total
            FROM convenios
            WHERE estado='Activo'
            GROUP BY en_ejecucion
        ");

        $data['graficoCarreras'] = $this->fetchAll($this->pdoCon, "
            SELECT carrera, COUNT(*) total
            FROM convenios
            WHERE estado='Activo'
            GROUP BY carrera
        ");

        $data['graficoCiudad'] = $this->fetchAll($this->pdoCon, "
            SELECT ciudad, COUNT(*) total
            FROM convenios
            WHERE estado='Activo'
            GROUP BY ciudad
        ");

        $data['mapaConvenios'] = $this->fetchAll($this->pdoCon, "
            SELECT nombre_empresa, ciudad, localizacion, tipo_convenio
            FROM convenios
            WHERE estado='Activo'
            AND localizacion IS NOT NULL
        ");

        $data['totalConvenios'] = $this->fetchOne($this->pdoCon, "
            SELECT COUNT(*) total
            FROM convenios
            WHERE estado='Activo'
        ")['total'];

        $data['conveniosVigentes'] = $this->fetchOne($this->pdoCon, "
            SELECT COUNT(*) total
            FROM convenios
            WHERE estado='Activo'
            AND estado_convenio='vigente'
        ")['total'];

        $data['conveniosEjecucion'] = $this->fetchOne($this->pdoCon, "
            SELECT COUNT(*) total
            FROM convenios
            WHERE estado='Activo'
            AND en_ejecucion='si'
        ")['total'];

        return $data;
    }
}
