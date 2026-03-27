<?php

require_once BASE_PATH . '/app/core/Model.php';

class PeriodoModel extends Model
{

    protected $table = 'periodos';

    public function create($data)
    {
        return $this->insert($data);
    }

    public function update($id, $data)
    {
        return parent::update($id, $data);
    }

    public function getActivo()
    {
        $stmt = $this->db->prepare("SELECT * FROM periodos WHERE estado = 'activo' LIMIT 1");
        $stmt->execute();
        return $stmt->fetch();
    }

    public function getAll()
    {
        $stmt = $this->db->prepare("SELECT * FROM periodos ORDER BY id DESC");
        $stmt->execute();
        return $stmt->fetchAll();
    }

        public function getById($id)
        {
            $stmt = $this->db->prepare("SELECT * FROM periodos WHERE id = :id");
            $stmt->execute(['id' => $id]);
            return $stmt->fetch();
        }

        public function delete($id)
        {
            $stmt = $this->db->prepare("DELETE FROM periodos WHERE id = :id");
            return $stmt->execute(['id' => $id]);
        }

    public function getByIndicador($indicador_id)
    {
        $stmt = $this->db->prepare("SELECT 
            p.* 
        FROM periodos p
        JOIN criterios c ON c.periodo_id = p.id
        JOIN indicadores i ON i.criterio_id = c.id
        WHERE i.id = :id
        LIMIT 1");

        $stmt->execute(['id' => $indicador_id]);
        return $stmt->fetch();
    }
}
