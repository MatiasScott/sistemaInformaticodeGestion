<?php

require_once BASE_PATH . '/app/config/database.php';

class Model
{

    protected $db;
    protected $table;

    public function __construct()
    {
        $database = new Database();
        $this->db = $database->connect();
    }

    // ==========================
    // MÉTODOS GENERALES
    // ==========================

    public function getAll()
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM {$this->table}");
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            // Tabla no encontrada u otro error de DB: devolver arreglo vacío para evitar fatal error
            error_log("Model::getAll error: " . $e->getMessage());
            return [];
        }
    }

    public function getById($id)
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE id = :id");
            $stmt->execute(['id' => $id]);
            return $stmt->fetch();
        } catch (PDOException $e) {
            error_log("Model::getById error: " . $e->getMessage());
            return null;
        }
    }

    public function delete($id)
    {
        $stmt = $this->db->prepare("DELETE FROM {$this->table} WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }

    protected function insert($data)
    {
        $fields = implode(",", array_keys($data));
        $placeholders = ":" . implode(",:", array_keys($data));

        $sql = "INSERT INTO {$this->table} ($fields) VALUES ($placeholders)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($data);

        return $this->db->lastInsertId();
    }

    protected function update($id, $data)
    {
        $fields = "";

        foreach ($data as $key => $value) {
            $fields .= "$key = :$key,";
        }

        $fields = rtrim($fields, ",");

        $sql = "UPDATE {$this->table} SET $fields WHERE id = :id";
        $data['id'] = $id;

        $stmt = $this->db->prepare($sql);
        return $stmt->execute($data);
    }
}
