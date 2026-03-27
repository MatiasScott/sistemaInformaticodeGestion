<?php

require_once BASE_PATH . '/app/core/Controller.php';
require_once BASE_PATH . '/app/models/RoleModel.php';

class RoleController extends Controller
{

    private $model;

    public function __construct()
    {
        parent::__construct();
        $this->model = new RoleModel();
    }

    public function index()
    {
        $this->authorize("role", "leer");
        $roles = $this->model->getAllWithCount();
        $this->view('roles/index', compact('roles'));
    }

    public function create()
    {
        $this->authorize("role", "crear");

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $rolId = $this->model->create(['nombre' => $_POST['nombre']]);

            if ($rolId) {
                $permisos = $_POST['permisos'] ?? [];
                $this->model->syncPermissions($rolId, $permisos);

                $this->log("roles", "crear", "Rol ID $rolId creado con permisos");
                header("Location: " . URL_PATH . "roles");
                exit;
            }
        }

        $permisos = $this->model->getAllPermissions();
        $this->view('roles/create', compact('permisos'));
    }

    public function edit($id)
    {
        $this->authorize("role", "actualizar");

        if ($_POST) {
            $this->model->updateRole($id, ['nombre' => $_POST['nombre']]);

            // Sincronizar permisos
            $permisosPost = $_POST['permisos'] ?? [];
            $this->model->syncPermissions($id, $permisosPost);

            $this->log("roles", "actualizar", "Rol ID $id actualizado");
            header("Location: " . URL_PATH . "role");
            exit;
        }

        $rol = $this->model->getById($id);

        // 1. Traer todos los permisos que existen en el sistema
        $permisos = $this->model->getAllPermissions();

        // 2. Traer los IDs de los permisos que ya tiene este rol
        $permisosAsignados = $this->model->getPermissionsByRole($id);

        $this->view('roles/edit', compact('rol', 'permisos', 'permisosAsignados'));
    }

    public function delete($id)
    {
        $this->authorize("role", "eliminar");
        $this->model->delete($id);
        $this->log("roles", "eliminar", "Rol ID $id eliminado");
        header("Location: " . URL_PATH . "role");
    }
}
