<?php

require_once BASE_PATH . '/app/core/Model.php';

class EscalaCualitativaModel extends Model
{
    protected $table = 'escala_cualitativa';

    public function create($data)
    {
        return parent::insert($data);
    }

    public function getAll()
    {
        try {
            $stmt = $this->db->prepare("
                SELECT ec.*, p.nombre as periodo_nombre
                FROM {$this->table} ec
                LEFT JOIN periodos p ON ec.periodo_id = p.id
            ");
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("EscalaCualitativaModel::getAll error: " . $e->getMessage());
            return [];
        }
    }

    public function update($id, $data)
    {
        return parent::update($id, $data);
    }

    public function getByPeriodo($periodo_id)
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE periodo_id = :periodo_id");
        $stmt->execute(['periodo_id' => $periodo_id]);
        return $stmt->fetchAll();
    }
}
