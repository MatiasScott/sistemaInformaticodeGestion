<?php

require_once BASE_PATH . '/app/core/Controller.php';
require_once BASE_PATH . '/app/models/IndicadorModel.php';
require_once BASE_PATH . '/app/models/SubcriterioModel.php';
require_once BASE_PATH . '/app/models/CargoModel.php';

class IndicadorController extends Controller
{

    private $model;

    public function __construct()
    {
        parent::__construct();
        $this->model = new IndicadorModel();
    }

    public function index()
    {
        $this->authorize("indicador", "leer");
        $indicadores = $this->model->getAll();

        // Calcular totales por subcriterio
        $subcriterios_totales = [];
        foreach ($indicadores as $i) {
            $subcriterio_id = $i['subcriterio_id'];
            if (!isset($subcriterios_totales[$subcriterio_id])) {
                $subcriterios_totales[$subcriterio_id] = [
                    'nombre' => $i['subcriterio_nombre'],
                    'total' => 0
                ];
            }
            $subcriterios_totales[$subcriterio_id]['total'] += $i['peso'];
        }

        $this->view('indicadores/index', compact('indicadores', 'subcriterios_totales'));
    }

    public function create()
    {
        $this->authorize("indicador", "crear");

        $error = null;

        if ($_POST) {

            // Validar peso total
            $peso_total = $this->model->getPesoTotalBySubcriterio($_POST['subcriterio_id']);
            if ($peso_total + $_POST['peso'] > 100) {
                $error = "El peso total de indicadores en este subcriterio no puede exceder 100%. Peso actual: {$peso_total}%, intentando agregar: {$_POST['peso']}%";
            } else {
                $data = $_POST;

                // Crear indicador
                $id = $this->model->create($data);

                // Guardar variables automáticamente si es cuantitativo
                if (!empty($data['tipo']) && $data['tipo'] === 'cuantitativo' && !empty($data['formula'])) {
                    $this->model->guardarVariables($id, $data['formula']);
                }

                $this->log("indicadores", "crear", "Indicador ID $id creado");

                header("Location: " . URL_PATH . "indicadores");
                exit;
            }
        }

        $subcriterioModel = new SubcriterioModel();
        $cargoModel = new CargoModel();
        $subcriterios = $subcriterioModel->getAll();
        $cargos = $cargoModel->getAll();

        $this->view('indicadores/create', compact('subcriterios', 'cargos', 'error'));
    }

    public function edit($id)
    {
        $this->authorize("indicador", "actualizar");

        $error = null;

        if ($_POST) {
            // Validar peso total
            $peso_total = $this->model->getPesoTotalBySubcriterio($_POST['subcriterio_id'], $id);
            if ($peso_total + $_POST['peso'] > 100) {
                $error = "El peso total de indicadores en este subcriterio no puede exceder 100%. Peso actual de otros indicadores: {$peso_total}%, intentando asignar: {$_POST['peso']}%";
            } else {
                $this->model->update($id, $_POST);

                // Recalcular variables si es cuantitativo
                if (!empty($_POST['tipo']) && $_POST['tipo'] === 'cuantitativo' && !empty($_POST['formula'])) {
                    $this->model->guardarVariables($id, $_POST['formula']);
                }
                $this->log("indicadores", "actualizar", "Indicador ID $id actualizado");
                header("Location: " . URL_PATH . "indicadores");
                exit;
            }
        }

        $subcriterioModel = new SubcriterioModel();
        $cargoModel = new CargoModel();

        // CAMBIO AQUÍ: Usar getFullById para traer el subcriterio_nombre
        $indicador = $this->model->getFullById($id);

        $subcriterios = $subcriterioModel->getAll();
        $cargos = $cargoModel->getAll();

        // Calcular peso disponible
        $peso_total_otros = $this->model->getPesoTotalBySubcriterio($indicador['subcriterio_id'], $id);
        $peso_disponible = 100 - $peso_total_otros;

        $this->view('indicadores/edit', compact('indicador', 'subcriterios', 'cargos', 'peso_total_otros', 'peso_disponible', 'error'));
    }

    public function delete($id)
    {
        $this->authorize("indicador", "eliminar");

        $this->model->delete($id);
        $this->log("indicadores", "eliminar", "Indicador ID $id eliminado");

        header("Location: " . URL_PATH . "indicadores");
    }
}
