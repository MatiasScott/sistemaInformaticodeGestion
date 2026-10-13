<?php

require_once BASE_PATH . '/app/core/Controller.php';
require_once BASE_PATH . '/app/models/EscalaCualitativaModel.php';
require_once BASE_PATH . '/app/models/PeriodoModel.php';

class EscalaCualitativaController extends Controller
{
    private function validarValor($valor): void
    {
        if (!is_numeric($valor) || (float) $valor < 0 || (float) $valor > 1) {
            http_response_code(400);
            die("El valor de la escala cualitativa debe estar entre 0 y 1.");
        }
    }

    public function index()
    {
        $this->authorize('escala-cualitativa', 'leer');

        $model = new EscalaCualitativaModel();
        $data = $model->getAll();

        $this->view('escala-cualitativa/index', ['data' => $data]);
    }

    public function create($id = null)
    {
        $this->authorize('escala-cualitativa', 'crear');

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            $this->validarValor($_POST['valor'] ?? null);
            $model = new EscalaCualitativaModel();

            if ($id) {
                // Actualizar
                $model->update($id, [
                    'nombre' => $_POST['nombre'],
                    'valor' => $_POST['valor'],
                    'estado' => $_POST['estado'],
                    'periodo_id' => $_POST['periodo_id']
                ]);

                $this->log("escala_cualitativa", "actualizar", "Escala actualizada: {$_POST['nombre']}");
                header("Location: " . URL_PATH . "escala-cualitativa");
            } else {
                // Crear
                $model->create([
                    'nombre' => $_POST['nombre'],
                    'valor' => $_POST['valor'],
                    'estado' => $_POST['estado'],
                    'periodo_id' => $_POST['periodo_id']
                ]);

                $this->log("escala_cualitativa", "crear", "Nueva escala creada: {$_POST['nombre']}");
                header("Location: " . URL_PATH . "escala-cualitativa");
            }
            exit();
        }

        $data = [];
        if ($id) {
            $model = new EscalaCualitativaModel();
            $data = $model->getById($id);
        }

        $periodoModel = new PeriodoModel();
        $periodos = $periodoModel->getAll();

        $this->view('escala-cualitativa/create', ['data' => $data, 'periodos' => $periodos]);
    }

    public function edit($id)
    {
        $this->authorize('escala-cualitativa', 'actualizar');

        $model = new EscalaCualitativaModel();
        $data = $model->getById($id);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->validarValor($_POST['valor'] ?? null);
            $model->update($id, [
                'nombre' => $_POST['nombre'],
                'valor' => $_POST['valor'],
                'estado' => $_POST['estado'],
                'periodo_id' => $_POST['periodo_id']
            ]);
            $this->log("escala_cualitativa", "actualizar", "Escala actualizada: {$_POST['nombre']}");
            header("Location: " . URL_PATH . "escala-cualitativa");
            exit();
        }

        $periodoModel = new PeriodoModel();
        $periodos = $periodoModel->getAll();

        // Cambiamos el nombre de la vista aquí
        $this->view('escala-cualitativa/edit', ['data' => $data, 'periodos' => $periodos]);
    }

    public function delete($id)
    {
        $this->authorize('escala-cualitativa', 'eliminar');

        $model = new EscalaCualitativaModel();
        $data = $model->getById($id);

        $model->delete($id);

        $this->log("escala_cualitativa", "eliminar", "Escala eliminada: {$data['nombre']}");

        header("Location: " . URL_PATH . "escala-cualitativa");
        exit();
    }
}
