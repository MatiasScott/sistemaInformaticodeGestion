<?php

require_once BASE_PATH . '/app/core/Controller.php';
require_once BASE_PATH . '/app/models/UserModel.php';

class AuthController extends Controller
{
    public function login()
{
    $error = null; // Inicializamos el error

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $userModel = new UserModel();
        $user = $userModel->findByEmail($_POST['email']);

        if (!$user || !password_verify($_POST['password'], $user['password'])) {
            $error = "Credenciales incorrectas"; // Guardamos el mensaje
        } else if ($user['estado'] !== 'activo') {
            $error = "Tu cuenta está inactiva. Contacta al administrador.";
        } else {
            // Si todo está bien, procedemos
            session_regenerate_id(true);
            $userCompleto = $userModel->getUserWithRolesAndPermissions($user['id']);
            $_SESSION['user_id'] = $userCompleto['id'];
            $_SESSION['LAST_ACTIVITY'] = time();
            $this->log("auth", "login", "Inicio de sesión");

            header("Location: " . URL_PATH . "dashboard");
            exit();
        }
    }

    // Pasamos la variable $error a la vista
    $this->view('auth/login', ['error' => $error]);
}


    public function logout()
    {
        $this->log("auth", "logout", "Cierre de sesión");

        // Eliminar todas las variables de sesión
        $_SESSION = array();

        // Si se usa una cookie de sesión, eliminarla
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }

        session_destroy();

        $this->redirect('auth/login');
    }
}
