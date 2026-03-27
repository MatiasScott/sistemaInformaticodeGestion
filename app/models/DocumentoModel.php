<?php

require_once BASE_PATH . '/app/core/Model.php';

class DocumentoModel extends Model
{

    protected $table = 'documentos';

    public function create($data)
    {
        return $this->insert($data);
    }

    public function getAll()
    {
        $sql = "SELECT 
            d.*,
            p.nombre as periodo_nombre,
            i.nombre as indicador_nombre,
            u.primer_nombre as subido_por_nombre
        FROM documentos d
        LEFT JOIN periodos p ON d.periodo_id = p.id
        LEFT JOIN indicadores i ON d.indicador_id = i.id
        LEFT JOIN users u ON d.subido_por = u.id
        ORDER BY d.id DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getByIndicador($indicador_id)
    {
        $stmt = $this->db->prepare("SELECT * FROM documentos WHERE indicador_id = :id");
        $stmt->execute(['id' => $indicador_id]);
        return $stmt->fetchAll();
    }

    public function getById($id)
    {
        $stmt = $this->db->prepare("SELECT * FROM documentos WHERE id = :id");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    }

    public function update($id, $data)
    {
        return parent::update($id, $data);
    }

    /**
     * Buscar documentos por filtros (nombre, proceso, subproceso, codigo)
     * Filters is an associative array with optional keys: nombre, proceso, subproceso, codigo
     */
    public function search($filters = [])
    {
        $where = [];
        $params = [];

        $sql = "SELECT 
            d.*, 
            p.nombre as periodo_nombre,
            i.nombre as indicador_nombre,
            u.primer_nombre as subido_por_nombre
        FROM documentos d
        LEFT JOIN periodos p ON d.periodo_id = p.id
        LEFT JOIN indicadores i ON d.indicador_id = i.id
        LEFT JOIN users u ON d.subido_por = u.id
        WHERE d.estado = 'aprobado'";

        if (!empty($filters['nombre'])) {
            $where[] = "d.nombre_archivo LIKE :nombre";
            $params['nombre'] = '%' . $filters['nombre'] . '%';
        }

        if (!empty($filters['proceso'])) {
            $where[] = "d.proceso LIKE :proceso";
            $params['proceso'] = '%' . $filters['proceso'] . '%';
        }

        if (!empty($filters['subproceso'])) {
            $where[] = "d.subproceso LIKE :subproceso";
            $params['subproceso'] = '%' . $filters['subproceso'] . '%';
        }

        if (!empty($filters['codigo'])) {
            $where[] = "d.codigo LIKE :codigo";
            $params['codigo'] = '%' . $filters['codigo'] . '%';
        }

        if (!empty($where)) {
            $sql .= " AND " . implode(' AND ', $where);
        }

        $sql .= " ORDER BY d.id DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function countAll()
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM documentos");
        $stmt->execute();
        return (int)$stmt->fetchColumn();
    }

}
