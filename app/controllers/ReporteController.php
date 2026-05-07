<?php

require_once BASE_PATH . '/app/core/Controller.php';
require_once BASE_PATH . '/app/models/ReporteModel.php';
require_once BASE_PATH . '/vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use Dompdf\Dompdf;
use Dompdf\Options;

class ReporteController extends Controller
{
    private $model;

    private function clearOutputBuffers()
    {
        while (ob_get_level() > 0) {
            @ob_end_clean();
        }
    }

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
        $perPage = 30;
        $totalRows = count($rows);
        $totalPages = max(1, (int)ceil($totalRows / $perPage));

        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        if ($page < 1) {
            $page = 1;
        }
        if ($page > $totalPages) {
            $page = $totalPages;
        }

        $offset = ($page - 1) * $perPage;
        $rows = array_slice($rows, $offset, $perPage);

        $this->log('reportes', 'leer', 'Consulta de reporte: ' . $reporteKey);

        $this->view('reportes/index', compact(
            'reportes',
            'reporteKey',
            'reporte',
            'rows',
            'page',
            'perPage',
            'totalRows',
            'totalPages'
        ));
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
            $this->exportPdf($reporteKey, $reporte, $rows);
            return;
        }

        http_response_code(400);
        die('Formato no soportado.');
    }

    private function exportPdf($reporteKey, $reporte, $rows)
    {
        $titulo = htmlspecialchars($reporte['titulo']);
        $descripcion = htmlspecialchars($reporte['descripcion']);
        $generado = date('d/m/Y H:i:s');
        $total = count($rows);

        // Filas de la tabla
        $tbody = '';
        foreach ($rows as $i => $row) {
            $bg = ($i % 2 === 0) ? '#ffffff' : '#f9fafb';
            $tbody .= "<tr style='background:{$bg}'>";
            foreach (array_keys($reporte['columnas']) as $columnKey) {
                $tbody .= '<td>' . htmlspecialchars((string)($row[$columnKey] ?? '')) . '</td>';
            }
            $tbody .= '</tr>';
        }

        // Cabeceras
        $thead = '<tr>';
        foreach ($reporte['columnas'] as $label) {
            $thead .= '<th>' . htmlspecialchars($label) . '</th>';
        }
        $thead .= '</tr>';

        $html = "
        <!DOCTYPE html>
        <html lang='es'>
        <head>
            <meta charset='UTF-8'>
            <style>
                body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 11px; color: #111; margin: 0; padding: 16px; }
                h1 { font-size: 16px; margin: 0 0 4px; }
                .meta { font-size: 10px; color: #555; margin-bottom: 12px; }
                table { width: 100%; border-collapse: collapse; font-size: 10px; }
                th { background: #1f2937; color: #fff; padding: 6px 8px; text-align: left; }
                td { border: 1px solid #d1d5db; padding: 5px 8px; }
                tr:nth-child(even) td { background: #f9fafb; }
            </style>
        </head>
        <body>
            <h1>{$titulo}</h1>
            <p class='meta'>{$descripcion}</p>
            <p class='meta'>Generado el {$generado} | Total registros: {$total}</p>
            <table>
                <thead>{$thead}</thead>
                <tbody>{$tbody}</tbody>
            </table>
        </body>
        </html>";

        $options = new Options();
        $options->set('defaultFont', 'DejaVu Sans');
        $options->set('isRemoteEnabled', false);

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();

        $filename = 'reporte_' . $reporteKey . '_' . date('Ymd_His') . '.pdf';

        $this->clearOutputBuffers();
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        echo $dompdf->output();
        exit;
    }

    private function exportExcel($reporteKey, $reporte, $rows)
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle(mb_substr($reporteKey, 0, 31));

        // Título del reporte
        $totalCols = count($reporte['columnas']);
        $lastCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($totalCols);

        $sheet->mergeCells("A1:{$lastCol}1");
        $sheet->setCellValue('A1', $reporte['titulo']);
        $sheet->getStyle('A1')->applyFromArray([
            'font' => ['bold' => true, 'size' => 13],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);
        $sheet->getRowDimension(1)->setRowHeight(24);

        // Subtítulo: generado + total
        $sheet->mergeCells("A2:{$lastCol}2");
        $sheet->setCellValue('A2', 'Generado el ' . date('d/m/Y H:i:s') . '  |  Total registros: ' . count($rows));
        $sheet->getStyle('A2')->applyFromArray([
            'font' => ['italic' => true, 'color' => ['rgb' => '6B7280']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);

        // Cabeceras
        $colIndex = 1;
        foreach ($reporte['columnas'] as $label) {
            $cell = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex) . '3';
            $sheet->setCellValue($cell, $label);
            $colIndex++;
        }

        $sheet->getStyle("A3:{$lastCol}3")->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1F2937']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);
        $sheet->getRowDimension(3)->setRowHeight(18);

        // Datos
        $rowNum = 4;
        foreach ($rows as $row) {
            $colIndex = 1;
            foreach (array_keys($reporte['columnas']) as $columnKey) {
                $cell = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex) . $rowNum;
                $value = (string)($row[$columnKey] ?? '');
                $sheet->setCellValueExplicit($cell, $value, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                $colIndex++;
            }

            // Alternar color de filas
            if ($rowNum % 2 === 0) {
                $sheet->getStyle("A{$rowNum}:{$lastCol}{$rowNum}")->applyFromArray([
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F9FAFB']],
                ]);
            }

            $rowNum++;
        }

        // Bordes generales
        $sheet->getStyle("A3:{$lastCol}" . ($rowNum - 1))->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => ['rgb' => 'D1D5DB'],
                ],
            ],
        ]);

        // Autoajuste de ancho de columnas
        for ($i = 1; $i <= $totalCols; $i++) {
            $col = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($i);
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $filename = 'reporte_' . $reporteKey . '_' . date('Ymd_His') . '.xlsx';

        $this->clearOutputBuffers();
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        header('Pragma: no-cache');
        header('Expires: 0');

        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }
}
