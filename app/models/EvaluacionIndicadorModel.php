<?php

require_once BASE_PATH . '/app/core/Model.php';

class EvaluacionIndicadorModel extends Model
{

    protected $table = 'evaluaciones_indicador';

    public function create($data)
    {
        try {
            return $this->insert($data);
        } catch (PDOException $e) {
            if (
                ($e->errorInfo[1] ?? null) == 1062 &&
                strpos($e->errorInfo[2] ?? '', 'uk_periodo_indicador') !== false
            ) {
                throw new DomainException(
                    "Ya existe una evaluación para este período e indicador. Revise la evaluación existente antes de crear otra.",
                    0,
                    $e
                );
            }
            throw $e;
        }
    }

    public function getAll()
    {
        $sql = "SELECT 
            e.*,
            p.nombre as periodo_nombre,
            i.nombre as indicador_nombre,
            i.responsable_ejecucion_cargo,
            i.responsable_evaluacion_cargo,
            u.primer_nombre as evaluado_por_nombre
        FROM evaluaciones_indicador e
        LEFT JOIN periodos p ON e.periodo_id = p.id
        LEFT JOIN indicadores i ON e.indicador_id = i.id
        LEFT JOIN users u ON e.evaluado_por = u.id
        ORDER BY e.id DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function update($id, $data)
    {
        return parent::update($id, $data);
    }

    /**
     * Promedio ponderado anual de PAO y PAE según el año de finalización.
     */
    public function getInstitutionalResults($startYear = 2024, $endYear = 2028)
    {
        $sql = "SELECT 
            YEAR(p.fecha_fin) as year,
            SUM(i.peso) AS total_peso,
            SUM(e.porcentaje_obtenido * i.peso) AS total_porcentaje_peso,
            CASE WHEN SUM(i.peso) > 0 THEN (SUM(e.porcentaje_obtenido * i.peso) / SUM(i.peso)) ELSE 0 END AS resultado
        FROM evaluaciones_indicador e
        JOIN indicadores i ON e.indicador_id = i.id
        JOIN periodos p ON e.periodo_id = p.id
        WHERE YEAR(p.fecha_fin) BETWEEN :start AND :end
            AND e.porcentaje_obtenido IS NOT NULL
        GROUP BY YEAR(p.fecha_fin)
        ORDER BY YEAR(p.fecha_fin) ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':start', (int) $startYear, PDO::PARAM_INT);
        $stmt->bindValue(':end', (int) $endYear, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Retorna resultados por indicador (suma de porcentaje ponderado / suma de peso)
     * Si $periodo_id es nulo, agrupa por indicador en todo el historial
     */
    public function getResultsByIndicator($periodo_id = null)
    {
        $params = [];
        $where = '';
        if ($periodo_id) {
            $where = 'WHERE e.periodo_id = :periodo_id';
            $params['periodo_id'] = $periodo_id;
        }

        $sql = "SELECT 
            i.id as indicador_id,
            i.nombre as indicador_nombre,
            SUM(i.peso) AS total_peso,
            SUM(e.porcentaje_obtenido * i.peso) AS total_porcentaje_peso,
            CASE WHEN SUM(i.peso) > 0 THEN (SUM(e.porcentaje_obtenido * i.peso) / SUM(i.peso)) ELSE 0 END AS resultado
        FROM evaluaciones_indicador e
        JOIN indicadores i ON e.indicador_id = i.id
        $where
        GROUP BY i.id
        ORDER BY i.nombre ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function countAll()
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM evaluaciones_indicador");
        $stmt->execute();
        return (int)$stmt->fetchColumn();
    }

    public function getFullById($id)
    {
        $sql = "SELECT 
                e.*, 
                p.nombre as periodo_nombre, 
                i.nombre as indicador_nombre,
                i.responsable_ejecucion_cargo,
                i.responsable_evaluacion_cargo
            FROM evaluaciones_indicador e
            LEFT JOIN periodos p ON e.periodo_id = p.id
            LEFT JOIN indicadores i ON e.indicador_id = i.id
            WHERE e.id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    }
}
