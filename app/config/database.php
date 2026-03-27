<?php

class Database
{

    private $host = "localhost";
    private $user = "root";
    private $pass = "Superarse.2025";
    private $charset = "utf8mb4";

    public function connect($database = "superarse_sig")
    {

        $dsn = "mysql:host={$this->host};dbname={$database};charset={$this->charset}";

        try {
            $pdo = new PDO($dsn, $this->user, $this->pass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
            ]);
        } catch (PDOException $e) {
            die("Error de conexión a {$database}: " . $e->getMessage());
        }

        return $pdo;
    }
}
