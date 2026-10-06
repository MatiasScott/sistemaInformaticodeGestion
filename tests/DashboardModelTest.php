<?php

require_once __DIR__ . '/../app/models/DashboardModel.php';

// Run with: php tests/DashboardModelTest.php (requires pdo_sqlite).
class DashboardFixtureStatement
{
    public function fetch($mode)
    {
        return [
            'nombre' => null,
            'periodo' => null,
            'total' => 0,
            'totalgraduado' => 0,
            'promedio' => null,
        ];
    }

    public function fetchAll($mode)
    {
        return [];
    }
}

class DashboardFixtureConnection
{
    private $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function query($sql)
    {
        if (preg_match('/\bFROM\s+convenios\b/i', $sql)) {
            return $this->pdo->query($sql);
        }

        return new DashboardFixtureStatement();
    }
}

function checkConvenioCounts($model, $expected)
{
    $data = $model->getDashboardData();

    foreach ($expected as $key => $value) {
        if ((int) $data[$key] !== $value) {
            throw new RuntimeException(
                $key . ': expected ' . $value . ', got ' . var_export($data[$key], true)
            );
        }
    }
}

set_error_handler(function ($severity, $message, $file, $line) {
    throw new ErrorException($message, 0, $severity, $file, $line);
});

$pdo = new PDO('sqlite::memory:');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$pdo->exec('
    CREATE TABLE convenios (
        estado TEXT,
        estado_convenio TEXT,
        en_ejecucion TEXT,
        tipo_convenio TEXT,
        tipo_institucion TEXT,
        carrera TEXT,
        ciudad TEXT,
        nombre_empresa TEXT,
        localizacion TEXT
    )
');

// Only unrelated dashboard queries are stubbed; convenio queries run against SQLite.
$connection = new DashboardFixtureConnection($pdo);
$reflection = new ReflectionClass(DashboardModel::class);
$model = $reflection->newInstanceWithoutConstructor();
foreach (['pdoSig', 'pdoCon', 'pdoSgpro'] as $name) {
    $property = $reflection->getProperty($name);
    $property->setAccessible(true);
    $property->setValue($model, $connection);
}

checkConvenioCounts($model, [
    'totalConvenios' => 0,
    'conveniosVigentes' => 0,
    'conveniosEjecucion' => 0,
]);

$pdo->exec("
    INSERT INTO convenios (estado, estado_convenio, en_ejecucion) VALUES
        ('Activo', 'caducado', 'no'),
        ('Activo', 'vigente', 'si'),
        ('Activo', 'vigente', 'no'),
        ('Activo', 'vigente', 'si'),
        ('Inactivo', 'vigente', 'si')
");
checkConvenioCounts($model, [
    'totalConvenios' => 4,
    'conveniosVigentes' => 3,
    'conveniosEjecucion' => 2,
]);

$pdo->exec("DELETE FROM convenios WHERE estado_convenio='vigente'");
checkConvenioCounts($model, [
    'totalConvenios' => 1,
    'conveniosVigentes' => 0,
    'conveniosEjecucion' => 0,
]);

$pdo->exec('DROP TABLE convenios');
try {
    $model->getDashboardData();
    throw new RuntimeException('Expected database errors to propagate.');
} catch (PDOException $exception) {
    // Database failures must not be reported as zero counts.
}

restore_error_handler();
echo "Dashboard convenio regression tests passed.\n";
