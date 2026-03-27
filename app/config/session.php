<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verificar si está logueado
if (isset($_SESSION['user_id'])) {

    if (isset($_SESSION['LAST_ACTIVITY'])) {

        if (time() - $_SESSION['LAST_ACTIVITY'] > SESSION_TIMEOUT) {
                session_unset();
                session_destroy();
                // Redirigir al login con flag de timeout
                header("Location: " . URL_PATH . "auth/login?timeout=1");
            exit();
        }
    }

    $_SESSION['LAST_ACTIVITY'] = time();
}
