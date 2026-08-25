<?php

class Database
{

    private $host = "localhost";
    private $user = "devists";
    private $pass = "Q@822111785849oz";
    private $charset = "utf8mb4";

    public function connect($database = "superarse_sig")
    {

        $dsn = "mysql:host={$this->host};dbname={$database};charset={$this->charset}";

        try {
            $pdo = new PDO($dsn, $this->user, $this->pass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
            ]);
        } catch (PDOException $e) {
            die("Error de conexión a {$database}: " . $e->getMessage());
        }

        return $pdo;
    }
}
