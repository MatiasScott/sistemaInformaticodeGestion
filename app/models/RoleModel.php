<?php

require_once BASE_PATH . '/app/core/Model.php';

class RoleModel extends Model
{

    protected $table = 'roles';

    public function create($data)
    {
        return $this->insert($data);
    }

    public function updateRole($id, $data)
    {
        return $this->update($id, $data);
    }

    public function deleteRole($id)
    {
        return $this->delete($id);
    }

    // Obtener todos los roles con conteo de permisos (para el index)
    public function getAllWithCount()
    {
        $sql = "SELECT r.*, 
                (SELECT COUNT(*) FROM role_permissions rp WHERE rp.role_id = r.id) as total_permisos 
                FROM roles r";
        return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    // Obtener todos los permisos que existen en el sistema (maestro)
    public function getAllPermissions()
    {
        return $this->db->query("SELECT * FROM permissions ORDER BY modulo, accion")->fetchAll(PDO::FETCH_ASSOC);
    }

    // Obtener solo los IDs de los permisos que tiene un rol específico
    public function getPermissionsByRole($roleId)
    {
        $stmt = $this->db->prepare("SELECT permission_id FROM role_permissions WHERE role_id = ?");
        $stmt->execute([$roleId]);
        return $stmt->fetchAll(PDO::FETCH_COLUMN); // Devuelve array simple de IDs
    }

    public function syncPermissions($roleId, $permissions)
    {
        // 1. Borrar actuales
        $this->db->prepare("DELETE FROM role_permissions WHERE role_id = ?")->execute([$roleId]);

        // 2. Insertar nuevos
        if (!empty($permissions)) {
            $stmt = $this->db->prepare("INSERT INTO role_permissions (role_id, permission_id) VALUES (?, ?)");
            foreach ($permissions as $pId) {
                $stmt->execute([$roleId, $pId]);
            }
        }
    }
}
