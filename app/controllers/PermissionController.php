<?php

require_once BASE_PATH . '/app/core/Controller.php';
require_once BASE_PATH . '/app/models/PermissionModel.php';

class PermissionController extends Controller
{

    private $model;

    public function __construct()
    {
        parent::__construct();
        $this->model = new PermissionModel();
    }

    public function index()
    {
        $this->model->syncModulesPermissions();
        $this->authorize("permission", "leer");
        $permisos = $this->model->getAll();
        $this->view('permissions/index', compact('permisos'));
    }

    public function create()
    {
        $this->authorize("permission", "crear");

        if ($_POST) {
            $id = $this->model->create($_POST);
            $this->log("permisos", "crear", "Permiso ID $id creado");
            // Importante: Verifica si tu ruta es 'permission' o 'permissions'
            header("Location: " . URL_PATH . "permission");
            exit;
        }

        // En lugar de SQL complejo, traemos todos y filtramos con PHP
        $todosLosPermisos = $this->model->getAll() ?? [];

        // Extraemos solo los nombres de los módulos y quitamos duplicados
        $modulos = array_unique(array_column($todosLosPermisos, 'modulo'));

        // Pasamos la variable a la vista
        $this->view('permissions/create', ['modulosExistentes' => $modulos]);
    }

    public function edit($id)
    {
        $this->authorize("permission", "actualizar");

        if ($_POST) {
            $this->model->update($id, $_POST);
            $this->log("permisos", "actualizar", "Permiso ID $id actualizado");
            header("Location: " . URL_PATH . "permission");
        }

        $permiso = $this->model->getById($id);
        $this->view('permissions/edit', compact('permiso'));
    }

    public function delete($id)
    {
        $this->authorize("permission", "eliminar");

        $this->model->delete($id);
        $this->log("permisos", "eliminar", "Permiso ID $id eliminado");

        header("Location: " . URL_PATH . "permission");
    }
}
