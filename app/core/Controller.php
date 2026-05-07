<?php

require_once BASE_PATH . '/app/models/UserModel.php';
require_once BASE_PATH . '/app/models/AuditoriaModel.php';

class Controller
{

    protected $user;
    protected $auditoria;

    public function __construct()
    {
        $this->auditoria = new AuditoriaModel();

        if (isset($_SESSION['user_id'])) {
            $userModel = new UserModel();
            $this->user = $userModel->getUserWithRolesAndPermissions($_SESSION['user_id']);
        }
    }

    // ===============================
    // AUTORIZACIÓN RBAC
    // ===============================

    private function canonModulo($modulo)
    {
        $key = strtolower(trim((string)$modulo));
        $key = str_replace(['-', ' '], '_', $key);

        $map = [
            'user' => 'usuarios',
            'users' => 'usuarios',
            'usuario' => 'usuarios',
            'usuarios' => 'usuarios',

            'role' => 'roles',
            'roles' => 'roles',

            'permission' => 'permisos',
            'permissions' => 'permisos',
            'permiso' => 'permisos',
            'permisos' => 'permisos',

            'cargo' => 'cargos',
            'cargos' => 'cargos',

            'periodo' => 'periodos',
            'periodos' => 'periodos',

            'escala_cualitativa' => 'escala_cualitativa',

            'gestor_documental' => 'documentos',
            'documento' => 'documentos',
            'documentos' => 'documentos',

            'criterio' => 'criterios',
            'criterios' => 'criterios',

            'subcriterio' => 'subcriterios',
            'subcriterios' => 'subcriterios',

            'indicador' => 'indicadores',
            'indicadores' => 'indicadores',

            'evaluacion' => 'evaluaciones',
            'evaluaciones' => 'evaluaciones',

            'plan_mejora' => 'plan_mejoras',
            'plan_mejoras' => 'plan_mejoras',
            'planmejoras' => 'plan_mejoras',

            'notificacion' => 'notificaciones',
            'notificaciones' => 'notificaciones',

            'reporte' => 'reportes',
            'reportes' => 'reportes',

            'dashboard' => 'dashboard'
        ];

        return $map[$key] ?? $key;
    }

    private function canonAccion($accion)
    {
        $key = strtolower(trim((string)$accion));

        $map = [
            'ver' => 'leer',
            'leer' => 'leer',
            'lectura' => 'leer',

            'crear' => 'crear',

            'actualizar' => 'actualizar',
            'editar' => 'actualizar',

            'eliminar' => 'eliminar',

            'escritura' => 'escritura',
            'descargar' => 'descargar',
            'aprobar' => 'aprobar'
        ];

        return $map[$key] ?? $key;
    }

    private function accionPermitida($accionRequerida, $accionPermiso)
    {
        if ($accionRequerida === $accionPermiso) {
            return true;
        }

        // escritura equivale a permisos operativos de modificación
        if ($accionRequerida === 'escritura' && in_array($accionPermiso, ['crear', 'actualizar', 'eliminar', 'escritura'], true)) {
            return true;
        }

        // Si un rol tiene "escritura", permite crear/actualizar/eliminar
        if (in_array($accionRequerida, ['crear', 'actualizar', 'eliminar'], true) && $accionPermiso === 'escritura') {
            return true;
        }

        return false;
    }

    protected function authorize($modulo = null, $accion = null)
    {
        if (!$this->user) {
            header("Location: " . URL_PATH . "auth/login");
            exit();
        }
/*
        // DEBUG: Vamos a ver qué tiene el usuario realmente

        echo "<pre>";
        print_r($this->user['roles']);
        echo "</pre>";
        die("Buscando el rol: " . 'Super Administrador');
*/

        // Super Administrador tiene acceso a todo
        if ($this->userTieneRol('Super Administrador')) {
            return true;
        }

        // Si no especifica módulo y acción, solo verifica que esté logueado
        if (!$modulo || !$accion) {
            return true;
        }

        $moduloRequerido = $this->canonModulo($modulo);
        $accionRequerida = $this->canonAccion($accion);

        // Verificar permisos específicos
        foreach ($this->user['permisos'] as $permiso) {
            $moduloPermiso = $this->canonModulo($permiso['modulo'] ?? '');
            $accionPermiso = $this->canonAccion($permiso['accion'] ?? '');

            if (
                $moduloPermiso === $moduloRequerido &&
                $this->accionPermitida($accionRequerida, $accionPermiso)
            ) {
                return true;
            }
        }

        // Acceso denegado - enviar respuesta JSON si es AJAX
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
            http_response_code(403);
            echo json_encode(['error' => 'Acceso denegado', 'message' => 'No tiene permiso para realizar esta acción']);
            exit();
        }

        // Para requests normales, mostrar modal
        http_response_code(403);
        $this->mostrarModalAccesoDenegado("No tiene permiso para acceder a este módulo.{$moduloRequerido} - {$accionRequerida}");
        exit();
    }

    // ===============================
    // AUDITORÍA
    // ===============================

    private function normalizeUtf8Text($value)
    {
        if (!is_string($value) || $value === '') {
            return $value;
        }

        // Si ya es UTF-8 válido, no tocar.
        if (preg_match('//u', $value)) {
            return $value;
        }

        if (function_exists('mb_convert_encoding')) {
            $converted = @mb_convert_encoding($value, 'UTF-8', 'Windows-1252,ISO-8859-1,UTF-8');
            if (is_string($converted) && $converted !== '' && preg_match('//u', $converted)) {
                return $converted;
            }
        }

        if (function_exists('iconv')) {
            $converted = @iconv('Windows-1252', 'UTF-8//IGNORE', $value);
            if (is_string($converted) && $converted !== '' && preg_match('//u', $converted)) {
                return $converted;
            }

            $converted = @iconv('ISO-8859-1', 'UTF-8//IGNORE', $value);
            if (is_string($converted) && $converted !== '' && preg_match('//u', $converted)) {
                return $converted;
            }
        }

        // Fallback defensivo: elimina bytes no imprimibles fuera de ASCII.
        return preg_replace('/[^\x09\x0A\x0D\x20-\x7E]/', '', $value);
    }

    protected function log($modulo, $accion, $descripcion = "")
    {
        try {
            $this->auditoria->registrar([
                'user_id' => $this->user['id'] ?? null,
                'modulo' => $this->normalizeUtf8Text((string)$modulo),
                'accion' => $this->normalizeUtf8Text((string)$accion),
                'descripcion' => $this->normalizeUtf8Text((string)$descripcion),
                'ip' => $_SERVER['REMOTE_ADDR'] ?? null
            ]);
        } catch (Throwable $e) {
            // La auditoría no debe romper la operación principal.
            error_log('Controller::log error: ' . $e->getMessage());
        }
    }

    protected function view($view, $data = [])
    {
        extract($data);
        require BASE_PATH . "/app/views/$view.php";
    }

    /**
     * Redirect helper to build URLs without double slashes
     */
    protected function redirect($path)
    {
        $base = rtrim(URL_PATH, '/');
        $target = $base . '/' . ltrim($path, '/');
        header("Location: " . $target);
        exit();
    }

    protected function userTieneRol($rolNombre)
    {
        if (empty($this->user['roles'])) return false;

        foreach ($this->user['roles'] as $rol) {
            // En tu UserModel, el fetchAll devuelve arrays con la llave ['nombre']
            if (isset($rol['nombre']) && $rol['nombre'] === $rolNombre) {
                return true;
            }
        }
        return false;
    }

    protected function obtenerCargosUsuario()
    {
        return array_column($this->user['cargos'], 'id');
    }

    // ===============================
    // MODAL DE ACCESO DENEGADO
    // ===============================

    protected function mostrarModalAccesoDenegado($mensaje = "No tiene permiso para acceder a este contenido")
    {
?>
        <!DOCTYPE html>
        <html>

        <head>
            <title>Acceso Denegado</title>
            <link rel="stylesheet" href="<?= URL_PATH ?>css/global.css">
            <link rel="stylesheet" href="<?= URL_PATH ?>css/modals.css">
        </head>

        <body>
            <div class="modal-overlay active">
                <div class="modal modal-error">
                    <div class="modal-header">
                        <h2>🚫 Acceso Denegado</h2>
                    </div>
                    <div class="modal-body">
                        <p><?= htmlspecialchars($mensaje) ?></p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" onclick="window.history.back();">Volver</button>
                        <a href="<?= URL_PATH ?>dashboard" class="btn btn-primary">Ir al Dashboard</a>
                    </div>
                </div>
            </div>
        </body>

        </html>
<?php
    }
}
