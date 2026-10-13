<?php

class Database
{
    private $host;
    private $database;
    private $user;
    private $pass;
    private $charset;

    public function __construct()
    {
        $envPath = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . '.env';
        $fileConfig = [];

        if (is_file($envPath)) {
            $parsedConfig = parse_ini_file($envPath, false, INI_SCANNER_RAW);
            if ($parsedConfig === false) {
                throw new RuntimeException("No se pudo leer el archivo de configuración .env.");
            }
            $fileConfig = $parsedConfig;
        }

        $readConfig = static function ($key) use ($fileConfig) {
            $environmentValue = getenv($key);
            if ($environmentValue !== false) {
                return (string) $environmentValue;
            }

            return array_key_exists($key, $fileConfig) ? (string) $fileConfig[$key] : null;
        };

        $this->host = $readConfig('DB_HOST');
        $this->database = $readConfig('DB_NAME');
        $this->user = $readConfig('DB_USER');
        $this->pass = $readConfig('DB_PASSWORD');
        $this->charset = $readConfig('DB_CHARSET') ?? 'utf8mb4';

        foreach (['DB_HOST' => $this->host, 'DB_NAME' => $this->database, 'DB_USER' => $this->user, 'DB_PASSWORD' => $this->pass] as $key => $value) {
            if ($value === null) {
                throw new RuntimeException("Falta configurar {$key} en .env o en las variables de entorno.");
            }
        }
    }

    public function connect($database = null)
    {
        $database = $database ?? $this->database;
        $dsn = "mysql:host={$this->host};dbname={$database};charset={$this->charset}";

        try {
            $pdo = new PDO($dsn, $this->user, $this->pass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES {$this->charset} COLLATE utf8mb4_unicode_ci"
            ]);
        } catch (PDOException $e) {
            die("Error de conexión a {$database}: " . $e->getMessage());
        }

        return $pdo;
    }
}
