<?php

require_once BASE_PATH . '/app/core/Model.php';

class PermissionModel extends Model
{

    protected $table = 'permissions';

    public function create($data)
    {
        return $this->insert($data);
    }

    public function update($id, $data)
    {
        return parent::update($id, $data);
    }

    public function getByRole($role_id)
    {
        $stmt = $this->db->prepare("SELECT * FROM permissions WHERE role_id = :role_id");
        $stmt->execute(['role_id' => $role_id]);
        return $stmt->fetchAll();
    }

    public function deleteByRole($role_id)
    {
        $stmt = $this->db->prepare("DELETE FROM permissions WHERE role_id = :role_id");
        return $stmt->execute(['role_id' => $role_id]);
    }

    public function syncModulesPermissions()
    {
        $modules = require BASE_PATH . '/app/config/modules.php';

        $acciones = ['leer', 'crear', 'actualizar', 'eliminar', 'aprobar'];

        foreach ($modules as $modulo) {
            foreach ($acciones as $accion) {

                $stmt = $this->db->prepare("
                INSERT IGNORE INTO permissions (modulo, accion)
                VALUES (:modulo, :accion)
            ");

                $stmt->execute([
                    'modulo' => $modulo,
                    'accion' => $accion
                ]);
            }
        }
    }
}
