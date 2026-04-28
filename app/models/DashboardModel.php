<?php

class DashboardModel
{

    private $pdoSig;
    private $pdoCon;
    private $pdoSgpro;

    public function __construct()
    {
        $db = new Database();

        $this->pdoSig = $db->connect("superarse_sig");
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

        /* =========================
        PERIODO ACTUAL
        ========================= */

        $data['ultimoPeriodo'] = $this->fetchOne($this->pdoCon, "
            SELECT MAX(periodo) periodo
            FROM users
        ")['periodo'];

        /* =========================
        KPIs
        ========================= */

        $data['totalEstudiantes'] = $this->fetchOne($this->pdoCon, "
            SELECT COUNT(*) total
            FROM users
            WHERE periodo = (SELECT MAX(periodo) FROM users)
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
            WHERE periodo = (SELECT MAX(periodo) FROM users)
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
            WHERE periodo = (SELECT MAX(periodo) FROM users)
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

        $data['AvanceProyectosInvestigacion'] = $this->fetchAll($this->pdoCon, "
            SELECT nombre_proyecto, porcentaje_avance
            FROM proyectos_administracion
            WHERE estado='ACTIVO'
            AND tipo_proyecto='INVESTIGACION'
            AND periodo_academico = (
                SELECT MAX(periodo_academico)
                FROM proyectos_administracion
                WHERE tipo_proyecto='INVESTIGACION'
            )
            ORDER BY porcentaje_avance DESC
        ");

        $data['AvanceProyectosVinculacion'] = $this->fetchAll($this->pdoCon, "
            SELECT nombre_proyecto, porcentaje_avance
            FROM proyectos_administracion
            WHERE estado='ACTIVO'
            AND tipo_proyecto='VINCULACION'
            AND periodo_academico = (
                SELECT MAX(periodo_academico)
                FROM proyectos_administracion
                WHERE tipo_proyecto='VINCULACION'
            )
            ORDER BY porcentaje_avance DESC
        ");

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
        ")['total'];

        $data['totalProfesoresTC'] = $this->fetchOne($this->pdoSgpro, "
            SELECT COUNT(*) total
            FROM users
            WHERE dedicacion='TIEMPO COMPLETO'
        ")['total'];

        $data['totalProfesoresMT'] = $this->fetchOne($this->pdoSgpro, "
            SELECT COUNT(*) total
            FROM users
            WHERE dedicacion='MEDIO TIEMPO'
        ")['total'];

        /* =========================
        BECAS
        ========================= */

        $data['totalBecados'] = $this->fetchOne($this->pdoCon, "
            SELECT COUNT(*) AS total
            FROM superar1_conectados.users
            WHERE estado_beca LIKE CONCAT('%', RIGHT(
                (SELECT periodo FROM users ORDER BY periodo DESC LIMIT 1), 2
            ), '%')
            AND periodo = (SELECT periodo FROM users ORDER BY periodo DESC LIMIT 1) AND estado ='Activo';
        ")['total'];

        /* =========================
        PEDI
        ========================= */

        $data['avancePedi'] = $this->fetchAll($this->pdoCon, "
            SELECT objetivo_estrategia, avance
            FROM pedi
            WHERE estado='activo'
        ");

        /* =========================
        POA
        ========================= */

        $data['avancePoa'] = $this->fetchAll($this->pdoCon, "
            SELECT nombre_area,
            SUM(CASE WHEN estado_actividad='Ejecutada' THEN presupuesto_anual ELSE 0 END) ejecutado,
            SUM(CASE WHEN estado_actividad='en progreso' THEN presupuesto_anual ELSE 0 END) progreso,
            SUM(CASE WHEN estado_actividad='no ejecutada' THEN presupuesto_anual ELSE 0 END) no_ejecutado
            FROM poa
            WHERE estado='activo'
            GROUP BY nombre_area
        ");

        $data['actividadesPoa'] = $this->fetchAll($this->pdoCon, "
            SELECT p.nombre_area, act.nombre_actividad, act.avance
            FROM poa_actividades AS act
            JOIN poa AS p ON p.id_poa = act.id_poa
            WHERE YEAR(fecha_inicio) BETWEEN
            (
                SELECT MIN(CAST(REGEXP_SUBSTR(periodo, '[0-9]{4}') AS UNSIGNED))
                FROM users
                WHERE periodo = (SELECT MAX(periodo) FROM users)
            )
            AND
            (
                SELECT MAX(CAST(REGEXP_SUBSTR(periodo, '[0-9]{4}') AS UNSIGNED))
                FROM users
                WHERE periodo = (SELECT MAX(periodo) FROM users)
            )
            ORDER BY avance DESC
        ");

        $data['areas'] = $this->fetchAll($this->pdoCon, "
            SELECT DISTINCT nombre_area
            FROM poa
            ORDER BY nombre_area
        ");

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
            SELECT estado_convenio, COUNT(*) total
            FROM convenios
            GROUP BY estado_convenio;
        ")['total'];

        $data['conveniosEjecucion'] = $this->fetchOne($this->pdoCon, "
            SELECT en_ejecucion ,COUNT(*) total
            FROM convenios
            group by en_ejecucion;
        ")['total'];

        return $data;
    }
}
