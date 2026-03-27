<?php

require_once BASE_PATH . '/app/core/Controller.php';
require_once BASE_PATH . '/app/models/IndicadorModel.php';
require_once BASE_PATH . '/app/models/EvaluacionIndicadorModel.php';
require_once BASE_PATH . '/app/models/PlanMejoraModel.php';
require_once BASE_PATH . '/app/models/PeriodoModel.php';
require_once BASE_PATH . '/app/models/SubcriterioModel.php';
require_once BASE_PATH . '/app/models/CriterioModel.php';

class EvaluacionController extends Controller
{
    private function canViewAllEvaluaciones()
    {
        return $this->userTieneRol('Super Administrador') || $this->userTieneRol('Administrador');
    }

    private function getUserCargoIds()
    {
        $cargos = $this->obtenerCargosUsuario();
        return array_map('intval', $cargos ?? []);
    }

    private function userCanAccessEvaluacion(array $evaluacion)
    {
        if ($this->canViewAllEvaluaciones()) {
            return true;
        }

        $cargosUsuario = $this->getUserCargoIds();
        if (empty($cargosUsuario)) {
            return false;
        }

        $cargoEjecucion = isset($evaluacion['responsable_ejecucion_cargo']) ? (int)$evaluacion['responsable_ejecucion_cargo'] : 0;
        $cargoEvaluacion = isset($evaluacion['responsable_evaluacion_cargo']) ? (int)$evaluacion['responsable_evaluacion_cargo'] : 0;

        return in_array($cargoEjecucion, $cargosUsuario, true) || in_array($cargoEvaluacion, $cargosUsuario, true);
    }

    private function evaluacionBloqueada(array $evaluacion)
    {
        $valor = trim((string)($evaluacion['valor_ingresado'] ?? ''));
        if ($valor === '' || $valor === '[]') {
            return false;
        }

        // Bloquear solo cuando ya fue editada al menos una vez despues de su creacion.
        $createdAt = $evaluacion['created_at'] ?? null;
        $updatedAt = $evaluacion['updated_at'] ?? null;

        if (empty($createdAt) || empty($updatedAt)) {
            return false;
        }

        $createdTs = strtotime((string)$createdAt);
        $updatedTs = strtotime((string)$updatedAt);

        if ($createdTs === false || $updatedTs === false) {
            return false;
        }

        return $updatedTs > $createdTs;
    }

    public function index()
    {
        $this->authorize("evaluacion", "leer");

        $evaluacionModel = new EvaluacionIndicadorModel();
        $evaluaciones = $evaluacionModel->getAll();

        $evaluaciones = array_values(array_filter($evaluaciones, function ($ev) {
            return $this->userCanAccessEvaluacion($ev);
        }));

        foreach ($evaluaciones as &$ev) {
            $ev['bloqueada'] = $this->evaluacionBloqueada($ev);
        }
        unset($ev);

        $this->view('evaluaciones/index', compact('evaluaciones'));
    }

    public function create()
    {
        $this->authorize("evaluacion", "crear");

        if ($_POST) {
            // Procesar formulario de creación
            $id = $this->procesarEvaluacion($_POST);
            $this->log("evaluaciones", "crear", "Evaluación ID $id creada");
            header("Location: " . URL_PATH . "evaluaciones");
        }

        $periodoModel = new PeriodoModel();
        $indicadorModel = new IndicadorModel();

        $periodos = $periodoModel->getAll();
        $indicadores = $indicadorModel->getAll();

        $this->view('evaluaciones/create', compact('periodos', 'indicadores'));
    }

    public function edit($id)
    {
        $this->authorize("evaluacion", "actualizar");

        // Instanciamos el modelo localmente como haces en tus otros métodos
        $evaluacionModel = new EvaluacionIndicadorModel();
        $evaluacion = $evaluacionModel->getFullById($id);

        if (!$evaluacion) {
            http_response_code(404);
            die("Evaluación no encontrada.");
        }

        if (!$this->userCanAccessEvaluacion($evaluacion)) {
            http_response_code(403);
            $this->mostrarModalAccesoDenegado('No tiene permiso para editar esta evaluación asignada a otro cargo.');
            exit();
        }

        if ($this->evaluacionBloqueada($evaluacion)) {
            header("Location: " . URL_PATH . "evaluaciones?msg=bloqueada");
            exit;
        }

        if ($_POST) {

            $data = $_POST;
            $data['indicador_id'] = $evaluacion['indicador_id'];

            $resultado = $this->recalcularEvaluacion($id, $data);

            $this->log("evaluaciones", "actualizar", "Evaluación ID $id recalculada");

            header("Location: " . URL_PATH . "evaluaciones");
            exit;
        }

        $periodoModel = new PeriodoModel();
        $indicadorModel = new IndicadorModel();

        $periodos = $periodoModel->getAll();
        $indicadores = $indicadorModel->getAll();

        $this->view('evaluaciones/edit', compact('evaluacion', 'periodos', 'indicadores'));
    }

    public function delete($id)
    {
        $this->authorize("evaluacion", "eliminar");

        $evaluacionModel = new EvaluacionIndicadorModel();
        $evaluacionModel->delete($id);
        $this->log("evaluaciones", "eliminar", "Evaluación ID $id eliminada");

        header("Location: " . URL_PATH . "evaluaciones");
    }

    public function evaluar($indicador_id)
    {
        // ===============================
        // 1️⃣ AUTORIZACIÓN
        // ===============================
        $this->authorize("evaluacion", "crear");

        $indicadorModel = new IndicadorModel();
        $evaluacionModel = new EvaluacionIndicadorModel();
        $planModel = new PlanMejoraModel();

        $indicador = $indicadorModel->getById($indicador_id);

        if (!$indicador) {
            die("Indicador no encontrado.");
        }

        // ===============================
        // VALIDAR RESPONSABLE
        // ===============================
        if ($this->userTieneRol('Responsable')) {

            $cargos_usuario = $this->obtenerCargosUsuario();

            if (!in_array($indicador['responsable_ejecucion_cargo'], $cargos_usuario)) {
                die("No puede evaluar este indicador.");
            }
        }

        // ===============================
        // VALIDACIONES
        // ===============================
        if (!isset($_POST['valor'], $_POST['periodo_id'])) {
            die("Datos incompletos.");
        }

        $valor = trim($_POST['valor']);
        $periodo_id = (int) $_POST['periodo_id'];
        $observaciones = htmlspecialchars($_POST['observaciones'] ?? '');

        if ($indicador['valor_estandar'] <= 0) {
            die("Valor estándar inválido.");
        }

        // ===============================
        // CÁLCULO SEGURO
        // ===============================

        if ($indicador['tipo'] === 'cuantitativo') {

            if (!is_numeric($valor)) {
                die("Valor inválido.");
            }

            $valor_calculado = (float) $valor;
        } else {
            // La evaluación cualitativa no usa escalas en este flujo.
            $valor_calculado = 0.0;
        }

        $porcentaje = ($valor_calculado / $indicador['valor_estandar']) * 100;
        $porcentaje = min(100, round($porcentaje, 2));

        $diferencia = $valor_calculado - $indicador['valor_estandar'];
        $estado = ($porcentaje >= 100) ? 'aprobado' : 'rechazado';

        // ===============================
        // GUARDAR EVALUACIÓN
        // ===============================
        $evaluacion_id = $evaluacionModel->create([
            'periodo_id' => $periodo_id,
            'indicador_id' => $indicador_id,
            'valor_ingresado' => $valor,
            'valor_calculado' => $valor_calculado,
            'porcentaje_obtenido' => $porcentaje,
            'diferencia' => $diferencia,
            'estado' => $estado,
            'observaciones' => $observaciones,
            'evaluado_por' => $this->user['id']
        ]);

        // Calcular avances
        $subcriterioModel = new SubcriterioModel();
        $criterioModel = new CriterioModel();
        $subcriterioModel->calcularAvance($indicador['subcriterio_id'], $periodo_id);
        $subcriterio = $subcriterioModel->getById($indicador['subcriterio_id']);
        $criterioModel->calcularAvance($subcriterio['criterio_id'], $periodo_id);

        // ===============================
        // PLAN DE MEJORA AUTOMÁTICO
        // ===============================
        if ($estado === 'rechazado') {

            $planModel->create([
                'indicador_id' => $indicador_id,
                'actividad' => 'Mejorar cumplimiento del indicador.',
                'fecha_inicio' => date('Y-m-d'),
                'fecha_fin' => date('Y-m-d', strtotime('+30 days')),
                'estado' => 'pendiente'
            ]);
        }

        // ===============================
        // AUDITORÍA
        // ===============================
        $this->log("evaluaciones", "crear", "Evaluación ID $evaluacion_id del indicador $indicador_id");

        header("Location: " . URL_PATH . "indicadores");
        exit;
    }

    public function getIndicadorDetails()
    {
        header('Content-Type: application/json');

        $id = $_GET['id'] ?? null;
        $periodo_id = $_GET['periodo_id'] ?? null;

        if (!$id || !$periodo_id) {
            echo json_encode(['error' => 'ID and periodo_id required']);
            return;
        }

        $indicadorModel = new IndicadorModel();
        $indicador = $indicadorModel->getById($id);

        if (!$indicador) {
            echo json_encode(['error' => 'Indicador not found']);
            return;
        }

        $response = [
            'tipo' => $indicador['tipo'],
            'formula' => $indicador['formula'] ?? '',
            'variables' => []
        ];

        if ($indicador['tipo'] === 'cuantitativo') {
            // Extract variables from formula
            preg_match_all('/[A-Z]/', $indicador['formula'], $matches);
            $response['variables'] = array_unique($matches[0]);
        }

        echo json_encode($response);
    }

    private function procesarEvaluacion($data)
    {
        $evaluacionModel = new EvaluacionIndicadorModel();
        $indicadorModel = new IndicadorModel();
        $planModel = new PlanMejoraModel();

        $indicador = $indicadorModel->getById($data['indicador_id']);

        if (!$indicador) {
            die("Indicador no válido.");
        }

        $periodo_id = (int) $data['periodo_id'];
        $observaciones = htmlspecialchars($data['observaciones'] ?? '');

        if ($indicador['valor_estandar'] <= 0) {
            die("Valor estándar inválido.");
        }

        // ================================
        // CUANTITATIVO
        // ================================
        if ($indicador['tipo'] === 'cuantitativo') {

            if (empty($data['variables']) || !is_array($data['variables'])) {
                die("Debe ingresar las variables.");
            }

            $variables = $data['variables'];

            // Validar que todas sean numéricas
            foreach ($variables as $key => $valor) {
                if (!is_numeric($valor)) {
                    die("Valor inválido en variable $key.");
                }
            }

            $formula = $indicador['formula'];

            // Reemplazar variables de forma segura (solo tokens completos)
            foreach ($variables as $variable => $valor) {
                $formula = preg_replace('/\\b' . preg_quote($variable, '/') . '\\b/', (string) $valor, $formula);
            }

            // Evaluar fórmula
            try {
                $valor_calculado = eval("return $formula;");
            } catch (Throwable $e) {
                die("Error en la fórmula.");
            }

            $valor_calculado = round($valor_calculado, $indicador['decimales']);

            // Guardar JSON para auditoría
            $valor_ingresado = json_encode($variables);
        }

        // ================================
        // CUALITATIVO
        // ================================
        else {
            $valor_calculado = 0.0;
            $valor_ingresado = null;
        }

        // ================================
        // CÁLCULOS FINALES
        // ================================

        $porcentaje = ($valor_calculado / $indicador['valor_estandar']) * 100;
        $porcentaje = min(100, round($porcentaje, 2));

        if ($porcentaje >= 100) {
            $estado = 'aprobado';
        } elseif ($porcentaje >= 80) {
            $estado = 'pendiente';
        } else {
            $estado = 'rechazado';
        }

        $diferencia = $valor_calculado - $indicador['valor_estandar'];

        // ================================
        // GUARDAR
        // ================================

        $evaluacion_id = $evaluacionModel->create([
            'periodo_id' => $periodo_id,
            'indicador_id' => $data['indicador_id'],
            'valor_ingresado' => $valor_ingresado,
            'valor_calculado' => $valor_calculado,
            'porcentaje_obtenido' => $porcentaje,
            'diferencia' => $diferencia,
            'estado' => $estado,
            'observaciones' => $observaciones,
            'evaluado_por' => $this->user['id']
        ]);

        // ================================
        // PLAN DE MEJORA
        // ================================

        if ($estado === 'rechazado') {
            $planModel->create([
                'indicador_id' => $data['indicador_id'],
                'actividad' => 'Mejorar cumplimiento del indicador.',
                'fecha_inicio' => date('Y-m-d'),
                'fecha_fin' => date('Y-m-d', strtotime('+30 days')),
                'estado' => 'pendiente'
            ]);
        }

        return $evaluacion_id;
    }

    private function recalcularEvaluacion($id, $data)
    {
        $evaluacionModel = new EvaluacionIndicadorModel();
        $indicadorModel = new IndicadorModel();
        $planModel = new PlanMejoraModel();

        $indicador = $indicadorModel->getById($data['indicador_id']);

        $periodo_id = (int) $data['periodo_id'];

        // ==== CUANTITATIVO ====
        if ($indicador['tipo'] === 'cuantitativo') {

            $variables = $data['variables'];

            foreach ($variables as $key => $valor) {
                if (!is_numeric($valor)) {
                    die("Valor inválido en variable $key.");
                }
            }

            $formula = $indicador['formula'];

            foreach ($variables as $variable => $valor) {
                $formula = preg_replace('/\\b' . preg_quote($variable, '/') . '\\b/', (string) $valor, $formula);
            }

            try {
                $valor_calculado = eval("return $formula;");
            } catch (Throwable $e) {
                die("Error en la fórmula.");
            }

            $valor_calculado = round($valor_calculado, $indicador['decimales']);
            $valor_ingresado = json_encode($variables);
        }

        // ==== CUALITATIVO ====
        else {
            $valor_calculado = 0.0;
            $valor_ingresado = null;
        }

        $porcentaje = min(100, round(($valor_calculado / $indicador['valor_estandar']) * 100, 2));

        if ($porcentaje >= 100) {
            $estado = 'aprobado';
        } elseif ($porcentaje >= 80) {
            $estado = 'pendiente';
        } else {
            $estado = 'rechazado';
        }

        $diferencia = $valor_calculado - $indicador['valor_estandar'];

        $evaluacionModel->update($id, [
            'valor_ingresado' => $valor_ingresado,
            'valor_calculado' => $valor_calculado,
            'porcentaje_obtenido' => $porcentaje,
            'diferencia' => $diferencia,
            'estado' => $estado,
            'observaciones' => htmlspecialchars($data['observaciones'] ?? '')
        ]);

        // Calcular avances
        $subcriterioModel = new SubcriterioModel();
        $criterioModel = new CriterioModel();
        $subcriterioModel->calcularAvance($indicador['subcriterio_id'], $periodo_id);
        $subcriterio = $subcriterioModel->getById($indicador['subcriterio_id']);
        $criterioModel->calcularAvance($subcriterio['criterio_id'], $periodo_id);

        if ($estado === 'rechazado') {
            $planModel->create([
                'indicador_id' => $data['indicador_id'],
                'actividad' => 'Mejorar cumplimiento del indicador.',
                'fecha_inicio' => date('Y-m-d'),
                'fecha_fin' => date('Y-m-d', strtotime('+30 days')),
                'estado' => 'pendiente'
            ]);
        }
    }
}
