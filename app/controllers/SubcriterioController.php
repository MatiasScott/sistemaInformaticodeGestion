<?php

require_once BASE_PATH . '/app/core/Controller.php';
require_once BASE_PATH . '/app/models/SubcriterioModel.php';

class SubcriterioController extends Controller
{

    private $model;

    public function __construct()
    {
        parent::__construct();
        $this->model = new SubcriterioModel();
    }

    public function index()
    {
        $this->authorize("subcriterio", "leer");
        $subcriterios = $this->model->getAll();

        // Calcular totales por criterio
        $criterios_totales = [];
        foreach ($subcriterios as $s) {
            $criterio_id = $s['criterio_id'];
            if (!isset($criterios_totales[$criterio_id])) {
                $criterios_totales[$criterio_id] = [
                    'nombre' => $s['criterio_nombre'],
                    'total' => 0
                ];
            }
            $criterios_totales[$criterio_id]['total'] += $s['peso'];
        }

        $this->view('subcriterio/index', compact('subcriterios', 'criterios_totales'));
    }

    public function create()
    {
        $this->authorize("subcriterio", "crear");

        $error = null;

        if ($_POST) {
            // Validar peso total
            $peso_total = $this->model->getPesoTotalByCriterio($_POST['criterio_id']);
            if ($peso_total + $_POST['peso'] > 100) {
                $error = "El peso total de subcriterios en este criterio no puede exceder 100%. Peso actual: {$peso_total}%, intentando agregar: {$_POST['peso']}%";
            } else {
                $id = $this->model->create($_POST);
                $this->log("subcriterios", "crear", "Subcriterio ID $id creado");
                header("Location: " . URL_PATH . "subcriterio");
                exit;
            }
        }

        require_once BASE_PATH . '/app/models/CriterioModel.php';
        $criterioModel = new \CriterioModel();
        $criterios = $criterioModel->getAll();
        $this->view('subcriterio/create', compact('criterios', 'error'));
    }

    public function edit($id)
    {
        $this->authorize("subcriterio", "actualizar");

        $error = null;

        if ($_POST) {
            // Validar peso total
            $peso_total = $this->model->getPesoTotalByCriterio($_POST['criterio_id'], $id);
            if ($peso_total + $_POST['peso'] > 100) {
                $error = "El peso total de subcriterios en este criterio no puede exceder 100%. Peso actual de otros subcriterios: {$peso_total}%, intentando asignar: {$_POST['peso']}%";
            } else {
                $this->model->update($id, $_POST);
                $this->log("subcriterios", "actualizar", "Subcriterio ID $id actualizado");
                header("Location: " . URL_PATH . "subcriterio");
                exit;
            }
        }

        require_once BASE_PATH . '/app/models/CriterioModel.php';
        $criterioModel = new \CriterioModel();
        $subcriterio = $this->model->getById($id);
        $criterios = $criterioModel->getAll();

        // Calcular peso disponible
        $peso_total_otros = $this->model->getPesoTotalByCriterio($subcriterio['criterio_id'], $id);
        $peso_disponible = 100 - $peso_total_otros;

        $this->view('subcriterio/edit', compact('subcriterio', 'criterios', 'peso_total_otros', 'peso_disponible', 'error'));
    }

    public function delete($id)
    {
        $this->authorize("subcriterio", "eliminar");

        $this->model->delete($id);
        $this->log("subcriterios", "eliminar", "Subcriterio ID $id eliminado");

        header("Location: " . URL_PATH . "subcriterio");
    }
}
