<?php

require_once BASE_PATH . '/app/core/Model.php';

class UserModel extends Model
{

    protected $table = 'users';

    private array $fillable = [
        'primer_nombre',
        'segundo_nombre',
        'primer_apellido',
        'segundo_apellido',
        'email',
        'password',
        'estado'
    ];

    private function sanitizeUserData($data)
    {
        return array_intersect_key($data, array_flip($this->fillable));
    }

    public function create($data)
    {
        $data = $this->sanitizeUserData($data);

        if (!empty($data['password'])) {
            $data['password'] = password_hash($data['password'], PASSWORD_BCRYPT);
        } else {
            unset($data['password']);
        }

        return $this->insert($data);
    }

    public function updateUser($id, $data)
    {
        $data = $this->sanitizeUserData($data);

        if (!empty($data['password'])) {
            $data['password'] = password_hash($data['password'], PASSWORD_BCRYPT);
        } else {
            unset($data['password']); // NO actualizar password
        }

        return $this->update($id, $data);
    }

    public function findByEmail($email)
    {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE email = :email");
        $stmt->execute(['email' => $email]);
        return $stmt->fetch();
    }

    public function getUserWithRolesAndPermissions($user_id)
    {
        $db = $this->db; // asumimos que Model tiene conexión

        // Usuario base
        $stmt = $db->prepare("SELECT * FROM users WHERE id = :id");
        $stmt->execute(['id' => $user_id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            return null;
        }

        // Roles
        $stmt = $db->prepare("
        SELECT r.id, r.nombre
        FROM user_roles ur
        JOIN roles r ON ur.role_id = r.id
        WHERE ur.user_id = :user_id
    ");
        $stmt->execute(['user_id' => $user_id]);
        $user['roles'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Permisos
        $stmt = $db->prepare("
        SELECT p.modulo, p.accion
        FROM user_roles ur
        JOIN role_permissions rp ON ur.role_id = rp.role_id
        JOIN permissions p ON rp.permission_id = p.id
        WHERE ur.user_id = :user_id
    ");
        $stmt->execute(['user_id' => $user_id]);
        $user['permisos'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Cargos
        $stmt = $db->prepare("
        SELECT c.id, c.nombre
        FROM user_cargos uc
        JOIN cargos c ON uc.cargo_id = c.id
        WHERE uc.user_id = :user_id
    ");
        $stmt->execute(['user_id' => $user_id]);
        $user['cargos'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $user;
    }

    public function syncRoles($userId, $roles)
    {
        $this->db->prepare("DELETE FROM user_roles WHERE user_id = ?")
            ->execute([$userId]);

        $stmt = $this->db->prepare(
            "INSERT INTO user_roles (user_id, role_id) VALUES (?, ?)"
        );

        foreach ($roles as $roleId) {
            $stmt->execute([$userId, $roleId]);
        }
    }

    public function syncCargos($userId, $cargos)
    {
        $this->db->prepare("DELETE FROM user_cargos WHERE user_id = ?")
            ->execute([$userId]);

        $stmt = $this->db->prepare(
            "INSERT INTO user_cargos (user_id, cargo_id) VALUES (?, ?)"
        );

        foreach ($cargos as $cargoId) {
            $stmt->execute([$userId, $cargoId]);
        }
    }

    public function getAllRoles()
    {
        $stmt = $this->db->query("SELECT * FROM roles ORDER BY nombre ASC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAllCargos()
    {
        $stmt = $this->db->query("SELECT * FROM cargos ORDER BY nombre ASC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAllWithDetails()
    {
        $sql = "SELECT 
                u.*,
                (SELECT GROUP_CONCAT(r.nombre SEPARATOR ', ') 
                 FROM user_roles ur 
                 JOIN roles r ON ur.role_id = r.id 
                 WHERE ur.user_id = u.id) as roles,
                (SELECT GROUP_CONCAT(c.nombre SEPARATOR ', ') 
                 FROM user_cargos uc 
                 JOIN cargos c ON uc.cargo_id = c.id 
                 WHERE uc.user_id = u.id) as cargos
            FROM users u";

        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
