<?php

class Router {

    public function run() {

        $url = $_GET['url'] ?? 'auth/login';
        $url = explode('/', filter_var(rtrim($url, '/'), FILTER_SANITIZE_URL));

        // Convertir guiones a CamelCase para nombres de controladores
        $segment = $url[0];
        $method = $url[1] ?? 'index';

        // Generar candidatos (plural y singular) y probar cuál existe
        $candidates = [];
        $candidates[] = $segment;
        // si termina en 'es' intentar eliminar 'es' (criterios -> criterio)
        if (preg_match('/es$/', $segment)) {
            $candidates[] = preg_replace('/es$/', '', $segment);
        }
        // si termina en 's' intentar eliminar 's' (users -> user)
        if (substr($segment, -1) === 's') {
            $candidates[] = rtrim($segment, 's');
        }

        $controllerFile = null;
        $controllerName = null;

        foreach ($candidates as $cand) {
            if (empty($cand)) continue;
            $candName = str_replace(' ', '', ucwords(str_replace('-', ' ', $cand))) . 'Controller';
            $candFile = __DIR__ . '/../controllers/' . $candName . '.php';
            if (file_exists($candFile)) {
                $controllerFile = $candFile;
                $controllerName = $candName;
                break;
            }
        }

        // Fallback: búsqueda case-insensitive en el directorio de controladores
        // (necesario en Linux donde el FS distingue mayúsculas/minúsculas)
        if (!$controllerFile) {
            $controllersDir = __DIR__ . '/../controllers/';
            foreach ($candidates as $cand) {
                if (empty($cand)) continue;
                $candName = str_replace(' ', '', ucwords(str_replace('-', ' ', $cand))) . 'Controller';
                foreach (scandir($controllersDir) as $file) {
                    if (strcasecmp($file, $candName . '.php') === 0) {
                        $controllerFile = $controllersDir . $file;
                        // Obtener el nombre real de clase desde el nombre de archivo
                        $controllerName = basename($file, '.php');
                        break 2;
                    }
                }
            }
        }

        if ($controllerFile) {
            require_once $controllerFile;
            $controller = new $controllerName();
            $params = array_slice($url, 2);

            if (method_exists($controller, $method)) {
                call_user_func_array([$controller, $method], $params);
            } else {
                die("Método no encontrado.");
            }
        } else {
            die("Controlador no encontrado.");
        }
    }
}
