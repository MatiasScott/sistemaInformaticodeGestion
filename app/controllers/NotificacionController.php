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

        $notificaciones = $this->model->getAll();
        $this->view('notificacion/index', compact('notificaciones'));
    }

    public function marcarLeido($id)
    {
        $this->authorize("notificacion", "actualizar");

        $this->model->marcarLeido($id);
        $this->log("notificaciones", "leer", "Notificación leída");

        header("Location: " . URL_PATH . "notificacion");
    }
}
