<?php

require_once BASE_PATH . '/app/core/Controller.php';
require_once BASE_PATH . '/app/models/ReporteModel.php';

class ReporteController extends Controller
{
    private $model;

    public function __construct()
    {
        parent::__construct();
        $this->model = new ReporteModel();
    }

    public function index()
    {
        $this->authorize('reportes', 'leer');

        $reportes = $this->model->getReportDefinitions();
        $reporteKey = $_GET['tipo'] ?? array_key_first($reportes);
        $reporte = $this->model->getReportDefinition($reporteKey);

        if (!$reporte) {
            http_response_code(404);
            die('Reporte no encontrado.');
        }

        $rows = $this->model->getReportData($reporteKey);
        $this->log('reportes', 'leer', 'Consulta de reporte: ' . $reporteKey);

        $this->view('reportes/index', compact('reportes', 'reporteKey', 'reporte', 'rows'));
    }

    public function export($reporteKey, $format)
    {
        $this->authorize('reportes', 'leer');

        $reporte = $this->model->getReportDefinition($reporteKey);
        if (!$reporte) {
            http_response_code(404);
            die('Reporte no encontrado.');
        }

        $rows = $this->model->getReportData($reporteKey);
        $format = strtolower($format);

        if ($format === 'excel') {
            $this->log('reportes', 'descargar', 'Exportó reporte en Excel: ' . $reporteKey);
            $this->exportExcel($reporteKey, $reporte, $rows);
            return;
        }

        if ($format === 'pdf') {
            $this->log('reportes', 'descargar', 'Exportó reporte en PDF: ' . $reporteKey);
            $this->view('reportes/print', compact('reporteKey', 'reporte', 'rows'));
            return;
        }

        http_response_code(400);
        die('Formato no soportado.');
    }

    private function exportExcel($reporteKey, $reporte, $rows)
    {
        $filename = 'reporte_' . $reporteKey . '_' . date('Ymd_His') . '.xls';

        header('Content-Type: application/vnd.ms-excel; charset=UTF-8');
        header('Content-Disposition: attachment; filename=' . $filename);
        header('Pragma: no-cache');
        header('Expires: 0');

        echo "<html><head><meta charset=\"UTF-8\"></head><body>";
        echo '<h2>' . htmlspecialchars($reporte['titulo']) . '</h2>';
        echo '<p>Generado el ' . date('d/m/Y H:i:s') . '</p>';
        echo '<table border="1" cellspacing="0" cellpadding="6">';
        echo '<thead><tr>';

        foreach ($reporte['columnas'] as $label) {
            echo '<th style="background:#f2f2f2;">' . htmlspecialchars($label) . '</th>';
        }

        echo '</tr></thead><tbody>';

        foreach ($rows as $row) {
            echo '<tr>';
            foreach (array_keys($reporte['columnas']) as $columnKey) {
                echo '<td>' . htmlspecialchars((string)($row[$columnKey] ?? '')) . '</td>';
            }
            echo '</tr>';
        }

        echo '</tbody></table></body></html>';
        exit;
    }
}
