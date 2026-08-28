<?php

require_once BASE_PATH . '/app/config/database.php';

class ReporteModel
{
    private $pdoCon;
    private $pdoSgpro;

    private $pdoSig;

    public function __construct()
    {
        $db = new Database();
        $this->pdoCon = $db->connect('superar1_conectados');
        $this->pdoSgpro = $db->connect('superar1_landing_sgpro');
        $this->pdoSig = $db->connect('superar1_sig');
    }

    public function getCurrentPeriod()
    {
        $stmt = $this->pdoSig->query("
            SELECT nombre
            FROM periodos
            WHERE estado = 'activo'
            ORDER BY id DESC
            LIMIT 1
        ");
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!empty($row['nombre'])) {
            return $row['nombre'];
        }

        $stmt = $this->pdoCon->query("SELECT MAX(periodo) AS periodo_actual FROM users");
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['periodo_actual'] ?? null;
    }

    public function getReportDefinitions()
    {
        $periodoActual = $this->getCurrentPeriod();

        return [
            'estudiantes_periodo_actual' => [
                'titulo' => 'Lista certificada de estudiantes del periodo actual',
                'descripcion' => 'Listado general de estudiantes registrados en el periodo académico vigente.',
                'columnas' => [
                    'codigo_matricula' => 'Matrícula',
                    'nombre_completo' => 'Nombre completo',
                    'numero_identificacion' => 'Identificación',
                    'programa' => 'Programa',
                    'jornada' => 'Jornada',
                    'correo_electronico' => 'Correo institucional',
                    'estado' => 'Estado',
                    'periodo' => 'Periodo'
                ],
                'periodo' => $periodoActual
            ],
            'estudiantes_graduados' => [
                'titulo' => 'Estudiantes graduados',
                'descripcion' => 'Listado histórico de estudiantes con estado Graduado.',
                'columnas' => [
                    'codigo_matricula' => 'Matrícula',
                    'nombre_completo' => 'Nombre completo',
                    'numero_identificacion' => 'Identificación',
                    'programa' => 'Programa',
                    'correo_electronico' => 'Correo institucional',
                    'fecha_matricula' => 'Fecha matrícula',
                    'periodo' => 'Periodo'
                ]
            ],
            'docentes' => [
                'titulo' => 'Listado de docentes',
                'descripcion' => 'Docentes registrados en SGPRO con rol docente.',
                'columnas' => [
                    'id' => 'ID',
                    'name' => 'Nombre',
                    'email' => 'Correo',
                    'phone' => 'Teléfono',
                    'dedicacion' => 'Dedicación',
                    'estado' => 'Estado'
                ]
            ],
            'estudiantes_beca' => [
                'titulo' => 'Estudiantes con beca',
                'descripcion' => 'Listado de estudiantes que tienen beca registrada en Conectados.',
                'columnas' => [
                    'codigo_matricula' => 'Matrícula',
                    'nombre_completo' => 'Nombre completo',
                    'programa' => 'Programa',
                    'tipo_beca' => 'Tipo de beca',
                    'nombre_beca' => 'Nombre de beca',
                    'porcentaje_beca' => 'Porcentaje',
                    'monto_beca' => 'Monto',
                    'periodo' => 'Periodo'
                ]
            ]
        ];
    }

    public function getReportDefinition($reportKey)
    {
        $definitions = $this->getReportDefinitions();
        return $definitions[$reportKey] ?? null;
    }

    public function getReportData($reportKey)
    {
        switch ($reportKey) {
            case 'estudiantes_periodo_actual':
                return $this->getStudentsCurrentPeriod();
            case 'estudiantes_graduados':
                return $this->getGraduatedStudents();
            case 'docentes':
                return $this->getTeachers();
            case 'estudiantes_beca':
                return $this->getScholarshipStudents();
            default:
                return [];
        }
    }

    private function getStudentsCurrentPeriod()
    {
        $periodo = $this->pdoCon->quote($this->getCurrentPeriod());
        $sql = "SELECT
                    codigo_matricula,
                    TRIM(CONCAT(
                        COALESCE(primer_nombre, ''), ' ',
                        COALESCE(segundo_nombre, ''), ' ',
                        COALESCE(primer_apellido, ''), ' ',
                        COALESCE(segundo_apellido, '')
                    )) AS nombre_completo,
                    numero_identificacion,
                    programa,
                    jornada,
                    correo_electronico,
                    estado,
                    periodo
                FROM users
                WHERE periodo = {$periodo}
                AND estado = 'Activo'
                AND programa NOT IN (
                    'AUTO EVALUACION',
                    'AUTO EVALUCION',
                    'SEGUIMIENTO DOCENTE',
                    'EJEMPLO 1',
                    'EJEMPLO'
                )
                ORDER BY programa ASC, primer_apellido ASC, primer_nombre ASC";

        return $this->pdoCon->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    private function getGraduatedStudents()
    {
        $sql = "SELECT
                    codigo_matricula,
                    TRIM(CONCAT(
                        COALESCE(primer_nombre, ''), ' ',
                        COALESCE(segundo_nombre, ''), ' ',
                        COALESCE(primer_apellido, ''), ' ',
                        COALESCE(segundo_apellido, '')
                    )) AS nombre_completo,
                    numero_identificacion,
                    programa,
                    correo_electronico,
                    fecha_matricula,
                    periodo
                FROM users
                WHERE estado = 'Graduado'
                ORDER BY programa ASC, primer_apellido ASC, primer_nombre ASC";

        return $this->pdoCon->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    private function getTeachers()
    {
        $sql = "SELECT DISTINCT
                    u.id,
                    u.name,
                    u.email,
                    u.phone,
                    u.dedicacion,
                    CASE WHEN u.active = 1 THEN 'Activo' ELSE 'Inactivo' END AS estado
                FROM users u
                INNER JOIN user_roles_pivot urp ON urp.user_id = u.id
                WHERE urp.role_id = 5
                AND u.dedicacion IN ('TIEMPO COMPLETO', 'TIEMPO PARCIAL', 'MEDIO TIEMPO')
                ORDER BY u.dedicacion ASC, u.name ASC";

        return $this->pdoSgpro->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    private function getScholarshipStudents()
    {
        $periodo = $this->pdoCon->quote($this->getCurrentPeriod());
        $sql = "SELECT
                    codigo_matricula,
                    TRIM(CONCAT(
                        COALESCE(primer_nombre, ''), ' ',
                        COALESCE(segundo_nombre, ''), ' ',
                        COALESCE(primer_apellido, ''), ' ',
                        COALESCE(segundo_apellido, '')
                    )) AS nombre_completo,
                    programa,
                    tipo_beca,
                    nombre_beca,
                    porcentaje_beca,
                    monto_beca,
                    periodo
                FROM users
                WHERE estado_beca LIKE CONCAT('%', RIGHT(
                {$periodo}, 2
            ), '%')
            AND periodo = {$periodo} AND estado ='Activo'
                ORDER BY periodo DESC, programa ASC, primer_apellido ASC, primer_nombre ASC";

        return $this->pdoCon->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }
}
