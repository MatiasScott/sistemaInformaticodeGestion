<?php

require_once BASE_PATH . '/app/core/Controller.php';
require_once BASE_PATH . '/app/models/DashboardModel.php';

class DashboardController extends Controller
{

    public function index()
    {

        if (!isset($_SESSION['user_id'])) {
            header("Location: " . URL_PATH . "auth/login");
            exit();
        }

        $this->log("dashboard", "acceso", "Acceso al dashboard");

        $dashboardModel = new DashboardModel();

        /* ========================
        USUARIO
        ======================== */

        $userId = $_SESSION['user_id'];
        $user = $dashboardModel->getUser($userId);

        /* ========================
        DATOS DASHBOARD
        ======================== */

        $data = $dashboardModel->getDashboardData();

        /* ========================
        AUTOEVALUACION
        ======================== */

        require_once BASE_PATH . '/app/models/EvaluacionIndicadorModel.php';
        require_once BASE_PATH . '/app/models/CriterioModel.php';

        $evalModel = new EvaluacionIndicadorModel();
        $criterioModel = new CriterioModel();

        $startYear = 2024;
        $endYear = 2028;

        $data['institutional'] = $this->agregarResultadosHistoricos(
            $evalModel->getInstitutionalResults($startYear, $endYear),
            $startYear,
            $endYear
        );
        $data['byIndicator'] = $evalModel->getResultsByIndicator();
        $data['byCriterio'] = $criterioModel->getCriterio();

        $data['startYear'] = $startYear;
        $data['endYear'] = $endYear;

        $data['user'] = $user;

        /* ========================
        RENDER
        ======================== */

        $this->view('dashboard/index', $data);
    }

    /**
     * Resultados anuales oficiales que no tienen evaluaciones registradas en el sistema.
     * Solo se usan en el gráfico anual institucional.
     */
    private function agregarResultadosHistoricos(array $resultados, int $startYear, int $endYear): array
    {
        $historicos = [
            2024 => 86.31,
        ];

        $porAnio = [];
        foreach ($resultados as $resultado) {
            $porAnio[(int) $resultado['year']] = $resultado;
        }

        foreach ($historicos as $anio => $valor) {
            if ($anio >= $startYear && $anio <= $endYear && !isset($porAnio[$anio])) {
                $porAnio[$anio] = ['year' => $anio, 'resultado' => $valor];
            }
        }

        ksort($porAnio);
        return array_values($porAnio);
    }
}
