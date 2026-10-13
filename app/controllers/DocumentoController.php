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

        if (!isset($_FILES['archivo'])) {
            die("Debe seleccionar un archivo.");
        }

        $archivo = $_FILES['archivo'];

        switch ($archivo['error']) {
            case UPLOAD_ERR_OK:
                break;

            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
                die("El archivo supera el tamaño máximo permitido de 8 MB.");

            case UPLOAD_ERR_PARTIAL:
                die("La carga del archivo quedó incompleta. Intente nuevamente.");

            case UPLOAD_ERR_NO_FILE:
                die("Debe seleccionar un archivo.");

            default:
                die("Ocurrió un error durante la carga del archivo.");
        }

        // Tamaño máximo 8 MB
        $maxSize = 8 * 1024 * 1024;
        if ($archivo['size'] > $maxSize) {
            die("El archivo supera el tamaño máximo permitido de 8 MB.");
        }

        // Extensiones y tipos MIME permitidos
        $tiposPermitidos = [
            'pdf'  => ['application/pdf'],
            'doc'  => ['application/msword'],
            'docx' => [
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'application/zip'
            ],
            'xls'  => [
                'application/vnd.ms-excel',
                'application/CDFV2',
                'application/x-cdf'
            ],
            'xlsx' => [
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'application/zip'
            ],
            'png'  => ['image/png'],
            'jpg'  => ['image/jpeg'],
            'jpeg' => ['image/jpeg'],
        ];

        $extension = strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));

        if (!isset($tiposPermitidos[$extension])) {
            die("Tipo de archivo no permitido.");
        }

        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $tipoArchivo = $finfo->file($archivo['tmp_name']);

        if (
            $tipoArchivo === false
            || !in_array($tipoArchivo, $tiposPermitidos[$extension], true)
        ) {
            die("El contenido del archivo no corresponde con un tipo permitido.");
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

            $rutaArchivoActual = BASE_PATH . "/public/uploads/" . ($documento['ruta_archivo'] ?? '');
            $archivoActualExiste = !empty($documento['ruta_archivo'])
                && is_file($rutaArchivoActual);

            $hayNuevoArchivo = isset($_FILES['archivo'])
                && $_FILES['archivo']['error'] !== UPLOAD_ERR_NO_FILE;

            // Si el registro existe pero perdió su archivo físico,
            // es obligatorio reponerlo antes de guardar cambios.
            if (!$archivoActualExiste && !$hayNuevoArchivo) {
                die("El archivo físico de este documento no está disponible. Debe seleccionar un archivo para reponerlo.");
            }

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
            if ($hayNuevoArchivo) {

                $archivo = $_FILES['archivo'];

                switch ($archivo['error']) {
                    case UPLOAD_ERR_OK:
                        break;

                    case UPLOAD_ERR_INI_SIZE:
                    case UPLOAD_ERR_FORM_SIZE:
                        die("El archivo supera el tamaño máximo permitido de 8 MB.");

                    case UPLOAD_ERR_PARTIAL:
                        die("La carga del archivo quedó incompleta. Intente nuevamente.");

                    default:
                        die("Ocurrió un error durante la carga del archivo.");
                }

                $maxSize = 8 * 1024 * 1024;

                if ($archivo['size'] > $maxSize) {
                    die("El archivo supera el tamaño máximo permitido de 8 MB.");
                }

                $tiposPermitidos = [
                    'pdf'  => ['application/pdf'],
                    'doc'  => ['application/msword'],
                    'docx' => [
                        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                        'application/zip'
                    ],
                    'xls'  => [
                        'application/vnd.ms-excel',
                        'application/CDFV2',
                        'application/x-cdf'
                    ],
                    'xlsx' => [
                        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                        'application/zip'
                    ],
                    'png'  => ['image/png'],
                    'jpg'  => ['image/jpeg'],
                    'jpeg' => ['image/jpeg'],
                ];

                $ext = strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));

                if (!isset($tiposPermitidos[$ext])) {
                    die("Tipo de archivo no permitido.");
                }

                $finfo = new finfo(FILEINFO_MIME_TYPE);
                $tipoArchivo = $finfo->file($archivo['tmp_name']);

                if (
                    $tipoArchivo === false
                    || !in_array($tipoArchivo, $tiposPermitidos[$ext], true)
                ) {
                    die("El contenido del archivo no corresponde con un tipo permitido.");
                }

                $nuevoNombre = uniqid('doc_') . "." . $ext;
                $ruta = BASE_PATH . "/public/uploads/" . $nuevoNombre;

                // Primero guardar correctamente el archivo nuevo.
                if (!move_uploaded_file($archivo['tmp_name'], $ruta)) {
                    die("No se pudo guardar el nuevo archivo. El documento anterior se conserva.");
                }

                // Solo después de guardar el nuevo eliminamos el anterior.
                $rutaAnterior = BASE_PATH . "/public/uploads/" . $documento['ruta_archivo'];

                if (
                    !empty($documento['ruta_archivo'])
                    && $rutaAnterior !== $ruta
                    && file_exists($rutaAnterior)
                ) {
                    unlink($rutaAnterior);
                }

                $data['nombre_archivo'] = $archivo['name'];
                $data['ruta_archivo'] = $nuevoNombre;
                $data['tipo_archivo'] = $tipoArchivo;
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
