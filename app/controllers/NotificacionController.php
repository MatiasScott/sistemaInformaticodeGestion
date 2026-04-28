<?php

require_once BASE_PATH . '/app/core/Controller.php';
require_once BASE_PATH . '/app/models/NotificacionModel.php';

class NotificacionController extends Controller
{

    private $model;

    public function __construct()
    {
        parent::__construct();
        $this->model = new NotificacionModel();
    }

    public function index()
    {
        $this->authorize("notificacion", "leer");

        $viewerId = (int)($this->user['id'] ?? 0);
        $canViewAll = $this->userTieneRol('Super Administrador') || $this->userTieneRol('Administrador');
        $allowedModules = $this->getNotificationModules();

        $notificaciones = $this->model->getForViewer($viewerId, $canViewAll, $allowedModules);
        $this->view('notificacion/index', compact('notificaciones', 'canViewAll'));
    }

    public function marcarLeido($id)
    {
        $this->authorize("notificacion", "actualizar");

        $viewerId = (int)($this->user['id'] ?? 0);
        $this->model->marcarLeidoParaUsuario((int)$id, $viewerId);
        $this->log("notificaciones", "leer", "Notificación leída");

        header("Location: " . URL_PATH . "notificacion");
    }

    public function marcarTodasLeidas()
    {
        $this->authorize("notificacion", "actualizar");

        $viewerId = (int)($this->user['id'] ?? 0);
        $this->model->marcarTodasLeidasParaUsuario($viewerId);
        $this->log("notificaciones", "actualizar", "Todas las notificaciones fueron marcadas como leídas");

        header("Location: " . URL_PATH . "notificacion");
    }

    private function getNotificationModules()
    {
        if (empty($this->user['permisos'])) {
            return [];
        }

        $modules = [];
        foreach ($this->user['permisos'] as $permiso) {
            $modulo = trim((string)($permiso['modulo'] ?? ''));
            if ($modulo !== '') {
                $modules[] = $modulo;
            }
        }

        return array_values(array_unique($modules));
    }
}
