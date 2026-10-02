<?php

// Detectar protocolo
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://";

// Dominio
define('URL_PATH', $protocol . $_SERVER['HTTP_HOST'] . '/');

// Ruta física del proyecto
define('BASE_PATH', dirname(__DIR__, 2));

// Tiempo máximo de inactividad (5 minutos)
define('SESSION_TIMEOUT', 300); // 300 segundos

ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);

//para produccion
ini_set('session.cookie_secure', 0);

