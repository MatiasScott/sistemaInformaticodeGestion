<?php

require_once BASE_PATH . '/app/core/Model.php';

class IndicadorModel extends Model
{

    protected $table = 'indicadores';

    public function create($data)
    {
        return $this->insert($data);
    }

    public function getAll()
    {
        $sql = "SELECT 
            i.*,
            s.nombre as subcriterio_nombre,
            c.nombre as criterio_nombre,
            p.nombre as periodo_nombre,
            c1.nombre as responsable_ejecucion_cargo,
            c2.nombre as responsable_evaluacion_cargo
        FROM indicadores i
        LEFT JOIN subcriterios s ON i.subcriterio_id = s.id
        LEFT JOIN criterios c ON s.criterio_id = c.id
        LEFT JOIN periodos p ON c.periodo_id = p.id
        LEFT JOIN cargos c1 ON i.responsable_ejecucion_cargo = c1.id
        LEFT JOIN cargos c2 ON i.responsable_evaluacion_cargo = c2.id
        ORDER BY i.id DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getBySubcriterio($subcriterio_id)
    {
        $stmt = $this->db->prepare("SELECT * FROM indicadores WHERE subcriterio_id = :id");
        $stmt->execute(['id' => $subcriterio_id]);
        return $stmt->fetchAll();
    }

    public function update($id, $data)
    {
        // Solo pasar columnas conocidas de la tabla indicadores
        $allowed = [
            'subcriterio_id', 'codigo', 'nombre', 'tipo', 'peso',
            'formula', 'valor_estandar', 'decimales',
            'responsable_ejecucion_cargo', 'responsable_evaluacion_cargo'
        ];

        $filtered = [];
        foreach ($allowed as $col) {
            if (array_key_exists($col, $data)) {
                // Convertir campos INT vacíos a NULL
                $filtered[$col] = ($data[$col] === '') ? null : $data[$col];
            }
        }

        return parent::update($id, $filtered);
    }

    public function countAll()
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM indicadores");
        $stmt->execute();
        return (int)$stmt->fetchColumn();
    }

    public function getFullById($id)
    {
        $sql = "SELECT 
                i.*,
                s.nombre as subcriterio_nombre,
                c.nombre as criterio_nombre
            FROM indicadores i
            LEFT JOIN subcriterios s ON i.subcriterio_id = s.id
            LEFT JOIN criterios c ON s.criterio_id = c.id
            WHERE i.id = :id";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    }

    public function guardarVariables($indicador_id, $formula)
    {
        try {
            // Eliminar variables anteriores (por si se edita)
            $stmt = $this->db->prepare("DELETE FROM indicador_variables WHERE indicador_id = :id");
            $stmt->execute(['id' => $indicador_id]);

            if (empty($formula)) {
                return;
            }

            // Detectar variables: secuencias de letras mayúsculas
            preg_match_all('/[A-Z]+/', $formula, $matches);
            $variables = array_unique($matches[0]);

            foreach ($variables as $var) {
                $stmt = $this->db->prepare(
                    "INSERT INTO indicador_variables (indicador_id, nombre_variable) VALUES (:indicador_id, :nombre_variable)"
                );
                $stmt->execute([
                    'indicador_id' => $indicador_id,
                    'nombre_variable' => $var
                ]);
            }

            error_log("[IndicadorModel] guardarVariables OK - indicador_id={$indicador_id}, vars=" . implode(',', $variables));
        } catch (PDOException $e) {
            error_log("[IndicadorModel] guardarVariables ERROR - indicador_id={$indicador_id}: " . $e->getMessage());
        }
    }

    public function getVariables($indicador_id)
    {
        $stmt = $this->db->prepare("
        SELECT * FROM indicador_variables 
        WHERE indicador_id = :id
    ");
        $stmt->execute(['id' => $indicador_id]);
        return $stmt->fetchAll();
    }

    public function getPesoTotalBySubcriterio($subcriterio_id, $exclude_id = null)
    {
        $sql = "SELECT SUM(peso) as total_peso FROM indicadores WHERE subcriterio_id = :subcriterio_id";
        $params = ['subcriterio_id' => $subcriterio_id];

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
