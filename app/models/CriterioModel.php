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

    /**
     * Aporte de cada criterio al resultado institucional, por año (año de fin del período, igual
     * que el gráfico anual). Aporte = Σ(porcentaje × peso del indicador) del criterio / Σ pesos
     * evaluados del año, de modo que la suma de los criterios de un año es el resultado anual.
     * Los criterios de distintos períodos se agrupan por su numeración ("1.", "2.", ...).
     */
    public function getAportePorAnio($startYear, $endYear)
    {
        $sql = "SELECT
                    YEAR(p.fecha_fin) AS anio,
                    c.nombre AS criterio,
                    SUM(i.peso) AS peso_evaluado,
                    SUM(e.porcentaje_obtenido * i.peso) AS puntaje
                FROM evaluaciones_indicador e
                JOIN indicadores i ON i.id = e.indicador_id
                JOIN subcriterios s ON s.id = i.subcriterio_id
                JOIN criterios c ON c.id = s.criterio_id
                JOIN periodos p ON p.id = e.periodo_id
                WHERE e.porcentaje_obtenido IS NOT NULL
                  AND YEAR(p.fecha_fin) BETWEEN :start AND :end
                GROUP BY YEAR(p.fecha_fin), c.nombre";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':start', (int) $startYear, PDO::PARAM_INT);
        $stmt->bindValue(':end', (int) $endYear, PDO::PARAM_INT);
        $stmt->execute();

        $grupos = [];
        $pesoAnual = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $fila) {
            $nombre = trim((string) $fila['criterio']);
            $clave = preg_match('/^(\d+)\s*\./', $nombre, $m) ? (int) $m[1] : mb_strtoupper($nombre);
            $anio = (int) $fila['anio'];

            $grupos[$clave]['criterio'] = $nombre;
            $grupos[$clave]['anios'][$anio]['peso'] = ($grupos[$clave]['anios'][$anio]['peso'] ?? 0) + (float) $fila['peso_evaluado'];
            $grupos[$clave]['anios'][$anio]['puntaje'] = ($grupos[$clave]['anios'][$anio]['puntaje'] ?? 0) + (float) $fila['puntaje'];
            $pesoAnual[$anio] = ($pesoAnual[$anio] ?? 0) + (float) $fila['peso_evaluado'];
        }

        uksort($grupos, fn($a, $b) => is_int($a) && is_int($b) ? $a <=> $b : strcmp((string) $a, (string) $b));
        $anios = array_keys($pesoAnual);
        sort($anios);

        $resultado = [];
        foreach ($grupos as $grupo) {
            foreach ($anios as $anio) {
                $valores = $grupo['anios'][$anio] ?? null;
                $total = $pesoAnual[$anio];
                $resultado[] = [
                    'anio' => $anio,
                    'criterio' => $grupo['criterio'],
                    'peso' => $valores && $total > 0 ? round($valores['peso'] * 100 / $total, 2) : null,
                    'avance' => $valores && $valores['peso'] > 0 ? round($valores['puntaje'] / $valores['peso'], 2) : null,
                    'aporte' => $valores && $total > 0 ? round($valores['puntaje'] / $total, 2) : null,
                ];
            }
        }

        return $resultado;
    }

    public function getCriterio()
    {
        $sql = "SELECT c.nombre as criterio,
                       ROUND(COALESCE(c.avance, 0), 2) as avance,
                       ROUND(c.peso, 2) as peso,
                       ROUND(COALESCE(c.avance, 0) * c.peso / 100, 2) as aporte,
                       p.nombre AS periodo_nombre
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
            $this->update($criterio_id, ['avance' => 0]);
            return;
        }

        $total_ponderado = 0;
        $peso_total = 0;

        foreach ($subcriterios as $subcriterio) {
            $peso = (float) ($subcriterio['peso'] ?? 0);
            $peso_total += $peso;

            if ($peso > 0) {
                $total_ponderado += (float) ($subcriterio['avance'] ?? 0) * $peso;
            }
        }

        $avance = $peso_total > 0
            ? round($total_ponderado / $peso_total, 2)
            : 0;

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
