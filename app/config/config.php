<?php

// Detectar protocolo
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://";

// Dominio y ruta base de la aplicación (raíz del dominio o subcarpeta de XAMPP)
$scriptDirectory = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/'));
$basePath = ($scriptDirectory === '/' || $scriptDirectory === '.')
    ? ''
    : rtrim($scriptDirectory, '/');
define('URL_PATH', $protocol . ($_SERVER['HTTP_HOST'] ?? 'localhost') . $basePath . '/');

// Ruta física del proyecto
define('BASE_PATH', dirname(__DIR__, 2));

// Tiempo máximo de inactividad (5 minutos)
define('SESSION_TIMEOUT', 300); // 300 segundos

ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);

//para produccion
ini_set('session.cookie_secure', 0);
