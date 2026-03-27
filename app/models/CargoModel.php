<?php

require_once BASE_PATH . '/app/core/Model.php';

class CargoModel extends Model
{

    protected $table = 'cargos';

    public function create($data)
    {
        return $this->insert($data);
    }

    public function updateCargo($id, $data)
    {
        return $this->update($id, $data);
    }

    public function getAll()
    {
        $sql = "SELECT * FROM cargos";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    public function getById($id)
    {
        $stmt = $this->db->prepare("SELECT * FROM cargos WHERE id = :id");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    }
    public function delete($id)
    {
        return $this->delete($id);
    }
    public function getByIndicador($indicador_id)
    {
        $stmt = $this->db->prepare("SELECT * FROM cargos WHERE indicador_id = :id");
        $stmt->execute(['id' => $indicador_id]);
        return $stmt->fetchAll();
    }
}
