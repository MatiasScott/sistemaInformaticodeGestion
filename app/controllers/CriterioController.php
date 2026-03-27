<?php

require_once BASE_PATH . '/app/core/Controller.php';
require_once BASE_PATH . '/app/models/CriterioModel.php';
require_once BASE_PATH . '/app/models/PeriodoModel.php';

class CriterioController extends Controller
{

    private $model;

    public function __construct()
    {
        parent::__construct();
        $this->model = new CriterioModel();
    }

    public function index()
    {
        $this->authorize("criterio", "leer");
        $criterios = $this->model->getAll();

        // Calcular totales por período
        $periodos_totales = [];
        foreach ($criterios as $c) {
            $periodo_id = $c['periodo_id'];
            if (!isset($periodos_totales[$periodo_id])) {
                $periodos_totales[$periodo_id] = [
                    'nombre' => $c['periodo_nombre'],
                    'total' => 0
                ];
            }
            $periodos_totales[$periodo_id]['total'] += $c['peso'];
        }

        $this->view('criterios/index', compact('criterios', 'periodos_totales'));
    }

    public function create()
    {
        $this->authorize("criterio", "crear");

        $error = null;

        if ($_POST) {
            // Validar peso total
            $peso_total = $this->model->getPesoTotalByPeriodo($_POST['periodo_id']);
            if ($peso_total + $_POST['peso'] > 100) {
                $error = "El peso total de criterios en este período no puede exceder 100%. Peso actual: {$peso_total}%, intentando agregar: {$_POST['peso']}%";
            } else {
                $id = $this->model->create($_POST);
                $this->log("criterios", "crear", "Criterio ID $id creado");
                header("Location: " . URL_PATH . "criterio");
                exit;
            }
        }

        $periodoModel = new PeriodoModel();
        $periodos = $periodoModel->getAll();
        $this->view('criterios/create', compact('periodos', 'error'));
    }

    public function edit($id)
    {
        $this->authorize("criterio", "actualizar");

        $error = null;

        if ($_POST) {
            // Validar peso total
            $peso_total = $this->model->getPesoTotalByPeriodo($_POST['periodo_id'], $id);
            if ($peso_total + $_POST['peso'] > 100) {
                $error = "El peso total de criterios en este período no puede exceder 100%. Peso actual de otros criterios: {$peso_total}%, intentando asignar: {$_POST['peso']}%";
            } else {
                $this->model->update($id, $_POST);
                $this->log("criterios", "actualizar", "Criterio ID $id actualizado");
                header("Location: " . URL_PATH . "criterio");
                exit;
            }
        }

        $periodoModel = new PeriodoModel();
        $criterio = $this->model->getById($id);
        $periodos = $periodoModel->getAll();

        // Calcular peso disponible
        $peso_total_otros = $this->model->getPesoTotalByPeriodo($criterio['periodo_id'], $id);
        $peso_disponible = 100 - $peso_total_otros;

        $this->view('criterios/edit', compact('criterio', 'periodos', 'peso_total_otros', 'peso_disponible', 'error'));
    }

    public function delete($id)
    {
        $this->authorize("criterio", "eliminar");

        $this->model->delete($id);
        $this->log("criterios", "eliminar", "Criterio ID $id eliminado");

        header("Location: " . URL_PATH . "criterio");
    }
}
