<?php

require_once BASE_PATH . '/app/core/Controller.php';
require_once BASE_PATH . '/app/models/DocumentoModel.php';
require_once BASE_PATH . '/app/models/PeriodoModel.php';
require_once BASE_PATH . '/app/models/IndicadorModel.php';
require_once BASE_PATH . '/app/models/EvaluacionIndicadorModel.php';

class DocumentoController extends Controller
{
    // Listar documentos
    public function index()
    {
        $this->authorize("documento", "leer");
        $docModel = new DocumentoModel();
        $documentos = $docModel->getAll();
        $this->view('documentos/index', compact('documentos'));
    }

    public function create()
    {
        $this->authorize("documento", "crear");
        // Cargar datos necesarios para el formulario (periodos, indicadores, etc)
        $periodoModel = new PeriodoModel();
        $indicadorModel = new IndicadorModel();
        $evaluacionModel = new EvaluacionIndicadorModel();
        $periodos = $periodoModel->getAll();
        $indicadores = $indicadorModel->getAll();
        $evaluaciones = $evaluacionModel->getAll();
        $this->view('documentos/create', compact('periodos', 'indicadores', 'evaluaciones'));
    }

    // Subir documentos (multi-upload)
    public function subir()
    {
        // 🔐 Verificar sesión y permiso
        $this->authorize('documento', 'crear');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('documentos');
        }

        // ==============================
        // VALIDAR ARCHIVO
        // ==============================

        if (!isset($_FILES['archivo']) || $_FILES['archivo']['error'] !== UPLOAD_ERR_OK) {
            die("Error al subir el archivo.");
        }

        $archivo = $_FILES['archivo'];

        // Tamaño máximo 5MB
        $maxSize = 5 * 1024 * 1024;
        if ($archivo['size'] > $maxSize) {
            die("El archivo supera el tamaño permitido (5MB).");
        }

        // Extensiones permitidas
        $extensionesPermitidas = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'png', 'jpg', 'jpeg'];

        $extension = strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));

        if (!in_array($extension, $extensionesPermitidas)) {
            die("Tipo de archivo no permitido.");
        }

        // ==============================
        // PROCESAR NOMBRE
        // ==============================

        $nombrePersonalizado = trim($_POST['nombre_archivo']);

        if (empty($nombrePersonalizado)) {
            die("Debe ingresar un nombre para el documento.");
        }

        // Limpiar nombre (quitar caracteres raros)
        $nombrePersonalizado = preg_replace('/[^A-Za-z0-9áéíóúÁÉÍÓÚñÑ_\- ]/', '', $nombrePersonalizado);

        $tipoArchivo = $archivo['type'];

        // Nombre físico único en servidor
        $nuevoNombre = uniqid('doc_') . '.' . $extension;

        $rutaDestino = BASE_PATH . '/public/uploads/' . $nuevoNombre;

        if (!move_uploaded_file($archivo['tmp_name'], $rutaDestino)) {
            die("Error al mover el archivo.");
        }

        // ==============================
        // GUARDAR EN BASE DE DATOS
        // ==============================

        $data = [
            'periodo_id' => $_POST['periodo_id'],
            'indicador_id' => $_POST['indicador_id'] ?: null,
            'evaluacion_id' => $_POST['evaluacion_id'] ?: null,
            'proceso' => $_POST['proceso'] ?: null,
            'subproceso' => $_POST['subproceso'] ?: null,
            'codigo' => $_POST['codigo'] ?: null,

            // Nombre visible
            'nombre_archivo' => $nombrePersonalizado,

            // Nombre real físico
            'ruta_archivo' => $nuevoNombre,

            'tipo_archivo' => $tipoArchivo,
            'estado' => $_POST['estado'] ?? 'pendiente',
            'observaciones' => $_POST['observaciones'] ?: null,
            'subido_por' => $this->user['id'],
        ];

        $documentoModel = new DocumentoModel();
        $documentoModel->create($data);

        // 📋 Auditoría
        $this->log('documentos', 'crear', 'Subió documento: ' . $nombrePersonalizado);

        // 🔁 Redirigir
        $this->redirect('documentos');
    }

    // Editar documento
    public function edit($id)
    {
        $this->authorize("documento", "actualizar");

        $docModel = new DocumentoModel();
        $documento = $docModel->getById($id);

        if (!$documento) {
            die("Documento no encontrado.");
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            // Datos base (sin tocar archivo todavía)
            $data = [
                'periodo_id' => $_POST['periodo_id'],
                'indicador_id' => $_POST['indicador_id'],
                'evaluacion_id' => $_POST['evaluacion_id'] ?: null,
                'proceso' => $_POST['proceso'] ?: null,
                'subproceso' => $_POST['subproceso'] ?: null,
                'codigo' => $_POST['codigo'] ?: null,
                'estado' => $_POST['estado'],
                'observaciones' => $_POST['observaciones'] ?: null
            ];

            // ===============================
            // SI SUBEN NUEVO ARCHIVO
            // ===============================
            if (!empty($_FILES['archivo']['name'])) {

                $allowed = ['application/pdf', 'image/jpeg', 'image/png'];
                $maxSize = 10 * 1024 * 1024;

                if (
                    in_array($_FILES['archivo']['type'], $allowed)
                    && $_FILES['archivo']['size'] <= $maxSize
                ) {

                    // 🔥 Eliminar archivo anterior
                    $rutaAnterior = BASE_PATH . "/public/uploads/" . $documento['ruta_archivo'];
                    if (file_exists($rutaAnterior)) {
                        unlink($rutaAnterior);
                    }

                    $ext = pathinfo($_FILES['archivo']['name'], PATHINFO_EXTENSION);
                    $nuevoNombre = uniqid('doc_') . "." . $ext;
                    $ruta = BASE_PATH . "/public/uploads/" . $nuevoNombre;

                    move_uploaded_file($_FILES['archivo']['tmp_name'], $ruta);

                    // Guardar nuevos datos de archivo
                    $data['nombre_archivo'] = $_FILES['archivo']['name'];
                    $data['ruta_archivo'] = $nuevoNombre;
                    $data['tipo_archivo'] = $_FILES['archivo']['type'];
                }
            }

            $docModel->update($id, $data);

            $this->log("documentos", "actualizar", "Documento ID $id actualizado");

            $this->redirect('documentos');
        }

        $periodoModel = new PeriodoModel();
        $indicadorModel = new IndicadorModel();
        $evaluacionModel = new EvaluacionIndicadorModel();

        $periodos = $periodoModel->getAll();
        $indicadores = $indicadorModel->getAll();
        $evaluaciones = $evaluacionModel->getAll();

        $this->view('documentos/edit', compact(
            'documento',
            'periodos',
            'indicadores',
            'evaluaciones'
        ));
    }

    // Eliminar documento
    public function delete($id)
    {
        $this->authorize("documento", "eliminar");
        $docModel = new DocumentoModel();
        $docModel->delete($id);
        $this->log("documentos", "eliminar", "Documento ID $id eliminado");
        $this->redirect('documentos');
    }

    public function ver($id)
    {
        $this->authorize("documento", "leer");

        $docModel = new DocumentoModel();
        $documento = $docModel->getById($id);

        if (!$documento) {
            die("Documento no encontrado.");
        }

        $ruta = BASE_PATH . "/public/uploads/" . $documento['ruta_archivo'];

        if (!file_exists($ruta)) {
            die("Archivo no existe en el servidor.");
        }

        // 🔎 Auditoría
        $this->log(
            "documentos",
            "ver",
            "Visualizó documento: " . $documento['nombre_archivo']
        );

        // Mostrar en navegador
        header("Content-Type: " . $documento['tipo_archivo']);
        header("Content-Disposition: inline; filename=\"" . $documento['nombre_archivo'] . "\"");
        readfile($ruta);
        exit;
    }

    public function descargar($id)
    {
        $this->authorize("documento", "leer");

        $docModel = new DocumentoModel();
        $documento = $docModel->getById($id);

        if (!$documento) {
            die("Documento no encontrado.");
        }

        $ruta = BASE_PATH . "/public/uploads/" . $documento['ruta_archivo'];

        if (!file_exists($ruta)) {
            die("Archivo no existe en el servidor.");
        }

        // 📥 Auditoría
        $this->log(
            "documentos",
            "descargar",
            "Descargó documento: " . $documento['nombre_archivo']
        );

        // Forzar descarga
        header("Content-Description: File Transfer");
        header("Content-Type: " . $documento['tipo_archivo']);
        header("Content-Disposition: attachment; filename=\"" . $documento['nombre_archivo'] . "\"");
        header("Content-Length: " . filesize($ruta));
        readfile($ruta);
        exit;
    }
}
