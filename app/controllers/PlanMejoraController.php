<?php

require_once BASE_PATH . '/app/core/Controller.php';
require_once BASE_PATH . '/app/models/PlanMejoraModel.php';
require_once BASE_PATH . '/app/models/IndicadorModel.php';

class PlanMejoraController extends Controller
{

    private $model;

    public function __construct()
    {
        parent::__construct();
        $this->model = new PlanMejoraModel();
    }

    public function index()
    {
        $this->authorize("plan-mejora", "leer");
        $planmejoras = $this->model->getAll();
        $this->view('planmejoras/index', compact('planmejoras'));
    }

    public function create()
    {
        $this->authorize("plan-mejora", "crear");

        if ($_POST) {
            $payload = $this->buildPayloadFromRequest($_POST, '');
            $id = $this->model->create($payload);
            $this->log("plan_mejoras", "crear", "Plan de mejora ID $id creado");
            header("Location: " . URL_PATH . "planmejoras");
            exit;
        }

        $indicadorModel = new IndicadorModel();
        $indicadores = $indicadorModel->getAll();
        $this->view('planmejoras/create', compact('indicadores'));
    }

    public function edit($id)
    {
        $this->authorize("plan-mejora", "actualizar");

        $planmejora = $this->model->getById($id);
        if (!$planmejora) {
            header("Location: " . URL_PATH . "planmejoras");
            exit;
        }

        if ($_POST) {
            $payload = $this->buildPayloadFromRequest($_POST, $planmejora['actividad'] ?? '');
            $this->model->updatePlan($id, $payload);
            $this->log("plan_mejoras", "actualizar", "Plan de mejora ID $id actualizado");
            header("Location: " . URL_PATH . "planmejoras");
            exit;
        }

        $indicadorModel = new IndicadorModel();
        $indicadores = $indicadorModel->getAll();
        $this->view('planmejoras/edit', compact('planmejora', 'indicadores'));
    }

    public function delete($id)
    {
        $this->authorize("plan-mejora", "eliminar");

        $this->model->delete($id);
        $this->log("plan_mejoras", "eliminar", "Plan de mejora ID $id eliminado");

        header("Location: " . URL_PATH . "planmejoras");
    }

    private function buildPayloadFromRequest($input, $existingActividadRaw = '')
    {
        // El avance y estado se calculan SIEMPRE desde las actividades, no desde el form
        // Así que ignoramos lo que venga en $_POST['avance'] o $_POST['estado']

        $existingActivities = $this->decodeActivities($existingActividadRaw);
        $existingMap = [];

        foreach ($existingActivities as $item) {
            if (!empty($item['key'])) {
                $existingMap[(string)$item['key']] = $item;
            }
        }

        // Leer arrays de actividades del formulario
        $keys = isset($input['actividades_key']) && is_array($input['actividades_key']) ? $input['actividades_key'] : [];
        $descripciones = isset($input['actividades_desc']) && is_array($input['actividades_desc']) ? $input['actividades_desc'] : [];
        $checkedValues = isset($input['actividades_checked']) && is_array($input['actividades_checked']) ? $input['actividades_checked'] : [];
        $checkedMap = array_fill_keys(array_map('strval', $checkedValues), true);

        $activities = [];
        $seenKeys = [];

        // Procesar actividades recibidas del formulario
        foreach ($descripciones as $idx => $descripcion) {
            $descripcion = trim((string)$descripcion);
            if ($descripcion === '') {
                continue;
            }

            $providedKey = isset($keys[$idx]) ? trim((string)$keys[$idx]) : '';
            $key = $providedKey !== '' ? $providedKey : uniqid('act_', true);
            
            // Una actividad previamente marcada no se puede desmarcar
            $checked = isset($existingMap[(string)$key]) && !empty($existingMap[(string)$key]['checked'])
                ? true
                : isset($checkedMap[(string)$key]);

            $activities[] = [
                'key' => (string)$key,
                'descripcion' => $descripcion,
                'checked' => $checked,
            ];
            $seenKeys[(string)$key] = true;
        }

        // Preservar actividades completadas que intentaron ser eliminadas
        foreach ($existingMap as $key => $item) {
            if (!empty($item['checked']) && !isset($seenKeys[$key])) {
                $activities[] = [
                    'key' => (string)$key,
                    'descripcion' => (string)($item['descripcion'] ?? ''),
                    'checked' => true,
                ];
            }
        }

        // Calcular avance automáticamente
        $total = count($activities);
        $done = 0;
        foreach ($activities as $item) {
            if (!empty($item['checked'])) {
                $done++;
            }
        }

        $avance = $total > 0 ? (int)round(($done * 100) / $total) : 0;
        $avance = max(0, min(100, $avance)); // Clamp entre 0 y 100

        // Calcular estado automáticamente
        if ($avance >= 100) {
            $estado = 'finalizado';
        } elseif ($avance > 0) {
            $estado = 'en_proceso';
        } else {
            $estado = 'pendiente';
        }

        return [
            'indicador_id' => $input['indicador_id'] ?? null,
            'actividad' => json_encode($activities, JSON_UNESCAPED_UNICODE),
            'fecha_inicio' => $input['fecha_inicio'] ?? null,
            'fecha_fin' => $input['fecha_fin'] ?? null,
            'peso' => isset($input['peso']) && $input['peso'] !== '' ? $input['peso'] : null,
            'avance' => $avance,
            'estado' => $estado,
        ];
    }

    private function decodeActivities($raw)
    {
        $raw = trim((string)$raw);
        if ($raw === '') {
            return [];
        }

        $decoded = json_decode($raw, true);
        if (!is_array($decoded)) {
            return [];
        }

        $activities = [];
        foreach ($decoded as $idx => $item) {
            if (!is_array($item)) {
                continue;
            }

            $descripcion = trim((string)($item['descripcion'] ?? ''));
            if ($descripcion === '') {
                continue;
            }

            $key = isset($item['key']) ? trim((string)$item['key']) : '';
            if ($key === '') {
                $key = 'act_' . $idx;
            }

            $activities[] = [
                'key' => $key,
                'descripcion' => $descripcion,
                'checked' => !empty($item['checked']),
            ];
        }

        return $activities;
    }
}
