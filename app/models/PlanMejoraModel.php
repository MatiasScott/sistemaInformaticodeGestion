<?php

require_once BASE_PATH . '/app/core/Model.php';

class PlanMejoraModel extends Model
{

    protected $table = 'plan_mejoras';

    public function create($data)
    {
        return $this->insert($data);
    }

    public function getAll()
    {
        $sql = "SELECT 
            pm.*,
            i.nombre as indicador_nombre
        FROM plan_mejoras pm
        LEFT JOIN indicadores i ON pm.indicador_id = i.id
        ORDER BY pm.id DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function updatePlan($id, $data)
    {
        return $this->update($id, $data);
    }

    public function countAll()
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM plan_mejoras");
        $stmt->execute();
        return (int)$stmt->fetchColumn();
    }
}
