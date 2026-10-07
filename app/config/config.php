<?php

/*
|--------------------------------------------------------------------------
| Detección de protocolo
|--------------------------------------------------------------------------
| Compatible con:
| - XAMPP mediante HTTP.
| - HTTPS directo.
| - HTTPS mediante Nginx Proxy Manager.
*/

// HTTPS directo.
$httpsDirecto = !empty($_SERVER['HTTPS'])
    && strtolower((string) $_SERVER['HTTPS']) !== 'off';

// Cabecera del proxy inverso.
// Solo se considera cuando la conexión llega desde una IP privada
// de la red Docker.
$remoteIp = $_SERVER['REMOTE_ADDR'] ?? '';

$proxyConfiable = filter_var(
    $remoteIp,
    FILTER_VALIDATE_IP,
    FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE
) === false && filter_var($remoteIp, FILTER_VALIDATE_IP) !== false;

$forwardedProto = strtolower(trim(
    explode(',', $_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '')[0]
));

$httpsProxy = $proxyConfiable && $forwardedProto === 'https';

// Protocolo definitivo.
$esHttps = $httpsDirecto || $httpsProxy;

$protocol = $esHttps ? 'https://' : 'http://';

// Dominio y URL base.
define('URL_PATH', $protocol . $_SERVER['HTTP_HOST'] . '/');

// Ruta física del proyecto.
define('BASE_PATH', dirname(__DIR__, 2));

// Tiempo máximo de inactividad: 5 minutos.
define('SESSION_TIMEOUT', 300);

// Configuración de sesiones.
ini_set('session.cookie_httponly', '1');
ini_set('session.use_only_cookies', '1');

// Cookie segura únicamente cuando la conexión original es HTTPS.
ini_set('session.cookie_secure', $esHttps ? '1' : '0');
