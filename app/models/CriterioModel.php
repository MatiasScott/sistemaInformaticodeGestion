<?php

require_once BASE_PATH . '/app/core/Model.php';
require_once BASE_PATH . '/app/models/SubcriterioModel.php';

class CriterioModel extends Model
{

    protected $table = 'criterios';

    public function create($data)
    {
        return $this->insert($data);
    }

    public function getAll()
    {
        $sql = "SELECT 
            c.*,
            p.nombre as periodo_nombre
        FROM criterios c
        LEFT JOIN periodos p ON c.periodo_id = p.id
        ORDER BY c.id DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getCriterio()
    {
        $sql = "SELECT c.nombre as criterio, c.avance as avance, p.nombre AS periodo_nombre
                FROM criterios c
                LEFT JOIN periodos p ON c.periodo_id = p.id
                WHERE p.id = (
                    SELECT MAX(id) 
                    FROM periodos
                )
                ORDER BY c.id DESC;";

        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getByPeriodo($periodo_id)
    {
        $stmt = $this->db->prepare("SELECT * FROM criterios WHERE periodo_id = :periodo_id");
        $stmt->execute(['periodo_id' => $periodo_id]);
        return $stmt->fetchAll();
    }

    public function update($id, $data)
    {
        return parent::update($id, $data);
    }

    public function calcularAvance($criterio_id, $periodo_id)
    {
        // Obtener subcriterios del criterio
        $subcriterioModel = new SubcriterioModel();
        $subcriterios = $subcriterioModel->getByCriterio($criterio_id);

        if (empty($subcriterios)) {
            return; // No hay subcriterios
        }

        $total_avance = 0;
        $count = 0;

        foreach ($subcriterios as $subcriterio) {
            if (isset($subcriterio['avance']) && $subcriterio['avance'] !== null) {
                $total_avance += $subcriterio['avance'];
                $count++;
            }
        }

        if ($count > 0) {
            $avance = round($total_avance / $count, 2);
        } else {
            $avance = 0;
        }

        // Actualizar el avance del criterio
        $this->update($criterio_id, ['avance' => $avance]);
    }

    public function getPesoTotalByPeriodo($periodo_id, $exclude_id = null)
    {
        $sql = "SELECT SUM(peso) as total_peso FROM criterios WHERE periodo_id = :periodo_id";
        $params = ['periodo_id' => $periodo_id];

        if ($exclude_id) {
            $sql .= " AND id != :exclude_id";
            $params['exclude_id'] = $exclude_id;
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch();

        return (float) ($result['total_peso'] ?? 0);
    }
}
