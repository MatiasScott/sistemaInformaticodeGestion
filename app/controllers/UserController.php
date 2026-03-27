<?php

require_once BASE_PATH . '/app/core/Controller.php';
require_once BASE_PATH . '/app/models/UserModel.php';

class UserController extends Controller
{

    private $model;

    public function __construct()
    {
        parent::__construct();
        $this->model = new UserModel();
    }

    public function index()
    {
        $this->authorize("user", "leer");
        $usuarios = $this->model->getAllWithDetails();
        $this->view('users/index', compact('usuarios'));
    }

    public function create()
    {
        $this->authorize("user", "crear");
        $error = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Validar si el correo ya existe
            $existingUser = $this->model->findByEmail($_POST['email']);

            if ($existingUser) {
                $error = "El correo electrónico ya está registrado en el sistema.";
            } else {
                // Proceder con la creación
                $userId = $this->model->create($_POST);

                if ($userId) {
                    $roles = $_POST['roles'] ?? [];
                    $cargos = $_POST['cargos'] ?? [];
                    $this->model->syncRoles($userId, $roles);
                    $this->model->syncCargos($userId, $cargos);

                    $this->log("usuarios", "crear", "Usuario creado con ID: $userId");
                    header("Location: " . URL_PATH . "users");
                    exit;
                }
            }
        }

        $roles = $this->model->getAllRoles();
        $cargos = $this->model->getAllCargos();

        $this->view('users/create', compact('roles', 'cargos', 'error'));
    }

    public function edit($id)
    {
        $this->authorize("user", "actualizar");

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            $this->model->updateUser($id, $_POST);
            $roles = $_POST['roles'] ?? [];
            $cargos = $_POST['cargos'] ?? [];
            $this->model->syncRoles($id, $roles);
            $this->model->syncCargos($id, $cargos);
            $this->log("usuarios", "actualizar", "Usuario ID $id actualizado");

            header("Location: " . URL_PATH . "users");
            exit;
        }

        $usuario = $this->model->getUserWithRolesAndPermissions($id);

        $rolesDisponibles = $this->model->getAllRoles();
        $cargosDisponibles = $this->model->getAllCargos();

        $this->view('users/edit', compact('usuario', 'rolesDisponibles', 'cargosDisponibles'));
    }

    public function delete($id)
    {
        $this->authorize("user", "eliminar");

        $this->model->delete($id);
        $this->log("usuarios", "eliminar", "Usuario ID $id eliminado");

        header("Location: " . URL_PATH . "user");
    }
}
