<?php

require_once BASE_PATH . '/app/core/Controller.php';
require_once BASE_PATH . '/app/models/PeriodoModel.php';

class PeriodoController extends Controller
{

    private $model;

    public function __construct()
    {
        parent::__construct();
        $this->model = new PeriodoModel();
    }

    public function index()
    {
        $this->authorize("periodo", "leer");
        $periodos = $this->model->getAll();
        $this->view('periodos/index', compact('periodos'));
    }

    public function create()
    {
        $this->authorize("periodo", "crear");

        if ($_POST) {
            $id = $this->model->create($_POST);
            $this->log("periodos", "crear", "Periodo ID $id creado");
            header("Location: " . URL_PATH . "periodo");
        }

        $this->view('periodos/create');
    }

    public function edit($id)
    {
        $this->authorize("periodo", "actualizar");

        if ($_POST) {
            $this->model->update($id, $_POST);
            $this->log("periodos", "actualizar", "Periodo ID $id actualizado");
            header("Location: " . URL_PATH . "periodo");
        }

        $periodo = $this->model->getById($id);
        $this->view('periodos/edit', compact('periodo'));
    }

    public function delete($id)
    {
        $this->authorize("periodo", "eliminar");

        $this->model->delete($id);
        $this->log("periodos", "eliminar", "Periodo ID $id eliminado");

        header("Location: " . URL_PATH . "periodo");
    }
}
