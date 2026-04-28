<?php

require_once BASE_PATH . '/app/core/Model.php';
require_once BASE_PATH . '/app/models/UserModel.php';
require_once BASE_PATH . '/app/models/RoleModel.php';

class NotificacionModel extends Model
{

    protected $table = 'notificaciones';

    private UserModel $userModel;
    private RoleModel $roleModel;

    public function __construct()
    {
        parent::__construct();
        $this->userModel = new UserModel();
        $this->roleModel = new RoleModel();
    }

    public function create($data)
    {
        return $this->insert($data);
    }

    public function getForViewer($viewerId, $canViewAll = false, array $allowedModules = [])
    {
        $viewerId = (int)$viewerId;
        if ($viewerId <= 0) {
            return [];
        }

        $this->syncFromAuditoria($viewerId, $canViewAll, $allowedModules);

        $stmt = $this->db->prepare("\n            SELECT id, mensaje, leido, created_at\n            FROM {$this->table}\n            WHERE user_id = :user_id\n            ORDER BY created_at DESC, id DESC\n        ");
        $stmt->execute(['user_id' => $viewerId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function countUnreadForViewer($viewerId, $canViewAll = false, array $allowedModules = [])
    {
        $viewerId = (int)$viewerId;
        if ($viewerId <= 0) {
            return 0;
        }

        $this->syncFromAuditoria($viewerId, $canViewAll, $allowedModules);

        $stmt = $this->db->prepare("\n            SELECT COUNT(*) total\n            FROM {$this->table}\n            WHERE user_id = :user_id AND COALESCE(leido, 0) = 0\n        ");
        $stmt->execute(['user_id' => $viewerId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return (int)($row['total'] ?? 0);
    }

    public function marcarLeido($id)
    {
        return $this->update($id, ['leido' => 1]);
    }

    public function marcarLeidoParaUsuario($id, $viewerId)
    {
        $stmt = $this->db->prepare("\n            UPDATE {$this->table}\n            SET leido = 1\n            WHERE id = :id AND user_id = :user_id\n        ");

        return $stmt->execute([
            'id' => (int)$id,
            'user_id' => (int)$viewerId,
        ]);
    }

    public function marcarTodasLeidasParaUsuario($viewerId)
    {
        $stmt = $this->db->prepare("\n            UPDATE {$this->table}\n            SET leido = 1\n            WHERE user_id = :user_id AND COALESCE(leido, 0) = 0\n        ");

        return $stmt->execute([
            'user_id' => (int)$viewerId,
        ]);
    }

    private function syncFromAuditoria($viewerId, $canViewAll, array $allowedModules = [])
    {
        $auditorias = $this->getRelevantAuditoriaRows($viewerId, $canViewAll, $allowedModules);
        if (empty($auditorias)) {
            return;
        }

        $existsStmt = $this->db->prepare("\n            SELECT id\n            FROM {$this->table}\n            WHERE user_id = :user_id AND mensaje = :mensaje AND created_at = :created_at\n            LIMIT 1\n        ");

        $insertStmt = $this->db->prepare("\n            INSERT INTO {$this->table} (user_id, mensaje, leido, created_at)\n            VALUES (:user_id, :mensaje, 0, :created_at)\n        ");

        foreach ($auditorias as $auditoria) {
            $mensaje = $this->buildMessage($auditoria);
            $createdAt = $auditoria['fecha'] ?? date('Y-m-d H:i:s');

            $existsStmt->execute([
                'user_id' => $viewerId,
                'mensaje' => $mensaje,
                'created_at' => $createdAt,
            ]);

            if ($existsStmt->fetch(PDO::FETCH_ASSOC)) {
                continue;
            }

            $insertStmt->execute([
                'user_id' => $viewerId,
                'mensaje' => $mensaje,
                'created_at' => $createdAt,
            ]);
        }
    }

    private function getRelevantAuditoriaRows($viewerId, $canViewAll, array $allowedModules = [])
    {
        $sql = "\n            SELECT a.id, a.user_id, a.modulo, a.accion, a.descripcion, a.fecha\n            FROM auditoria a\n            WHERE a.accion IN ('crear', 'actualizar', 'eliminar')\n            ORDER BY a.fecha DESC, a.id DESC\n            LIMIT 300\n        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute();

        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if ($canViewAll) {
            return $rows;
        }

        $allowedModules = $this->normalizeModules($allowedModules);

        return array_values(array_filter($rows, function ($row) use ($viewerId, $allowedModules) {
            $rowUserId = (int)($row['user_id'] ?? 0);
            if ($rowUserId === (int)$viewerId) {
                return true;
            }

            if (empty($allowedModules)) {
                return false;
            }

            $modulo = $this->canonModulo($row['modulo'] ?? '');
            return in_array($modulo, $allowedModules, true);
        }));
    }

    private function buildMessage($auditoria)
    {
        $actor = $this->resolveActorName($auditoria['user_id'] ?? null);
        $accion = $this->humanizeAction($auditoria['accion'] ?? 'actualizar');
        $moduloCanon = $this->canonModulo($auditoria['modulo'] ?? 'sistema');
        $modulo = $this->humanizeModule($moduloCanon);
        $detalle = $this->resolveReadableDescription($moduloCanon, $auditoria['descripcion'] ?? '');

        $mensaje = $actor . ' ' . $accion . ' en ' . $modulo;
        if ($detalle !== '') {
            $mensaje .= ': ' . $detalle;
        }

        return $mensaje;
    }

    private function resolveActorName($userId)
    {
        $userId = (int)$userId;
        if ($userId <= 0) {
            return 'Un usuario';
        }

        $user = $this->userModel->getById($userId);
        if (!$user) {
            return 'Usuario #' . $userId;
        }

        $nombre = trim(($user['primer_nombre'] ?? '') . ' ' . ($user['primer_apellido'] ?? ''));
        return $nombre !== '' ? $nombre : 'Usuario #' . $userId;
    }

    private function resolveReadableDescription($modulo, $descripcion)
    {
        $descripcion = trim((string)$descripcion);
        if ($descripcion === '') {
            return '';
        }

        if ($modulo === 'usuarios') {
            return $this->resolveUserDescription($descripcion);
        }

        if ($modulo === 'roles') {
            return $this->resolveRoleDescription($descripcion);
        }

        return $descripcion;
    }

    private function resolveUserDescription($descripcion)
    {
        if (!preg_match('/(?:Usuario\s+creado\s+con\s+ID:|Usuario\s+ID)\s*(\d+)/i', $descripcion, $matches)) {
            return $descripcion;
        }

        $userId = (int)$matches[1];
        $user = $this->userModel->getById($userId);
        if (!$user) {
            return 'Usuario ID ' . $userId;
        }

        $nombre = trim(($user['primer_nombre'] ?? '') . ' ' . ($user['primer_apellido'] ?? ''));
        if ($nombre === '') {
            return 'Usuario ID ' . $userId;
        }

        return 'Usuario: ' . $nombre . ' (ID ' . $userId . ')';
    }

    private function resolveRoleDescription($descripcion)
    {
        if (!preg_match('/Rol\s+ID\s*(\d+)/i', $descripcion, $matches)) {
            return $descripcion;
        }

        $roleId = (int)$matches[1];
        $role = $this->roleModel->getById($roleId);
        if (!$role) {
            return 'Rol ID ' . $roleId;
        }

        $nombre = trim((string)($role['nombre'] ?? ''));
        if ($nombre === '') {
            return 'Rol ID ' . $roleId;
        }

        return 'Rol: ' . $nombre . ' (ID ' . $roleId . ')';
    }

    private function humanizeAction($accion)
    {
        $map = [
            'crear' => 'creó',
            'actualizar' => 'actualizó',
            'eliminar' => 'eliminó',
        ];

        return $map[strtolower((string)$accion)] ?? 'realizó una acción';
    }

    private function humanizeModule($modulo)
    {
        $modulo = $this->canonModulo($modulo);
        $modulo = str_replace('_', ' ', $modulo);
        return $modulo !== '' ? ucfirst($modulo) : 'Sistema';
    }

    private function normalizeModules(array $modules)
    {
        $normalized = [];

        foreach ($modules as $module) {
            $canon = $this->canonModulo($module);
            if ($canon !== '') {
                $normalized[] = $canon;
            }
        }

        return array_values(array_unique($normalized));
    }

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
            'dashboard' => 'dashboard',
        ];

        return $map[$key] ?? $key;
    }
}
