<?php

require_once BASE_PATH . '/app/core/Model.php';
require_once BASE_PATH . '/app/models/IndicadorModel.php';

class SubcriterioModel extends Model {

    protected $table = 'subcriterios';

    public function create($data)
    {
        return $this->insert($data);
    }

    public function getAll()
    {
        $sql = "SELECT 
            s.*,
            c.nombre as criterio_nombre
        FROM subcriterios s
        LEFT JOIN criterios c ON s.criterio_id = c.id
        ORDER BY s.id DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getByCriterio($criterio_id)
    {
        $stmt = $this->db->prepare("SELECT * FROM subcriterios WHERE criterio_id = :criterio_id");
        $stmt->execute(['criterio_id' => $criterio_id]);
        return $stmt->fetchAll();
    }

    public function getById($id)
    {
        $stmt = $this->db->prepare("SELECT * FROM subcriterios WHERE id = :id");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    }

    public function update($id, $data)
    {
        return parent::update($id, $data);
    }

    public function calcularAvance($subcriterio_id, $periodo_id)
    {
        // Obtener indicadores del subcriterio
        $indicadorModel = new IndicadorModel();
        $indicadores = $indicadorModel->getBySubcriterio($subcriterio_id);

        if (empty($indicadores)) {
            return; // No hay indicadores, no calcular
        }

        $total_porcentaje = 0;
        $count = 0;

        foreach ($indicadores as $indicador) {
            // Obtener la evaluación más reciente del indicador en el periodo
            $evaluacion = $this->getEvaluacionIndicador($indicador['id'], $periodo_id);
            if ($evaluacion) {
                $total_porcentaje += $evaluacion['porcentaje_obtenido'];
                $count++;
            }
        }

        if ($count > 0) {
            $avance = round($total_porcentaje / $count, 2);
        } else {
            $avance = 0; // O null, pero según usuario, entre 0 y 100
        }

        // Actualizar el avance del subcriterio
        $this->update($subcriterio_id, ['avance' => $avance]);
    }

    private function getEvaluacionIndicador($indicador_id, $periodo_id)
    {
        $stmt = $this->db->prepare("
            SELECT * FROM evaluaciones_indicador 
            WHERE indicador_id = :indicador_id AND periodo_id = :periodo_id 
            ORDER BY id DESC LIMIT 1
        ");
        $stmt->execute(['indicador_id' => $indicador_id, 'periodo_id' => $periodo_id]);
        return $stmt->fetch();
    }

    public function getPesoTotalByCriterio($criterio_id, $exclude_id = null)
    {
        $sql = "SELECT SUM(peso) as total_peso FROM subcriterios WHERE criterio_id = :criterio_id";
        $params = ['criterio_id' => $criterio_id];

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
