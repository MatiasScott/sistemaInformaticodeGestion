<?php

require_once BASE_PATH . '/app/core/Controller.php';
require_once BASE_PATH . '/app/models/DocumentoModel.php';

class GestorDocumentalController extends Controller
{
    // Página pública de búsqueda (acceso sin login)
    public function publicSearch()
    {
        $results = [];
        if (!empty($_GET)) {
            $docModel = new DocumentoModel();
            $filters = [
                'nombre' => $_GET['nombre'] ?? null,
                'proceso' => $_GET['proceso'] ?? null,
                'subproceso' => $_GET['subproceso'] ?? null,
                'codigo' => $_GET['codigo'] ?? null,
            ];
            $results = $docModel->search($filters);
        }

        $this->view('gestor-documental/public_search', compact('results'));
    }

    // Página interna (requiere login)
    public function index()
    {
        $this->authorize('gestor-documental', 'leer');

        $docModel = new DocumentoModel();
        $filters = [
            'nombre' => $_GET['nombre'] ?? null,
            'proceso' => $_GET['proceso'] ?? null,
            'subproceso' => $_GET['subproceso'] ?? null,
            'codigo' => $_GET['codigo'] ?? null,
        ];
        $results = $docModel->search($filters);

        $this->view('gestor-documental/index', compact('results'));
    }
}
