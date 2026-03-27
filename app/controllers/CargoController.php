<?php

require_once BASE_PATH . '/app/core/Controller.php';
require_once BASE_PATH . '/app/models/CargoModel.php';

class CargoController extends Controller
{

    private $model;

    public function __construct()
    {
        parent::__construct();
        $this->model = new CargoModel();
    }

    public function index()
    {
        $this->authorize("cargo", "leer");
        $cargos = $this->model->getAll();
        $this->view('cargos/index', compact('cargos'));
    }

    public function create()
    {
        $this->authorize("cargo", "crear");

        if ($_POST) {
            $id = $this->model->create($_POST);
            $this->log("cargos", "crear", "Cargo ID $id creado");
            header("Location: " . URL_PATH . "cargo");
        }

        $this->view('cargos/create');
    }

    public function edit($id)
    {
        $this->authorize("cargo", "actualizar");

        if ($_POST) {
            $this->model->updateCargo($id, $_POST);
            $this->log("cargos", "actualizar", "Cargo ID $id actualizado");
            header("Location: " . URL_PATH . "cargo");
        }

        $cargo = $this->model->getById($id);
        $this->view('cargos/edit', compact('cargo'));
    }

    public function delete($id)
    {
        $this->authorize("cargo", "eliminar");

        $this->model->delete($id);
        $this->log("cargos", "eliminar", "Cargo ID $id eliminado");

        header("Location: " . URL_PATH . "cargo");
    }
}
