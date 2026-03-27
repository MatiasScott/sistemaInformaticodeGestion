<!DOCTYPE html>
<html>

<head>
    <title>Sistema Informático de Gestión</title>
    <!-- CSS Global -->
    <link rel="stylesheet" href="<?= URL_PATH ?>css/global.css">

    <!-- CSS Específicos de Módulos -->
    <link rel="stylesheet" href="<?= URL_PATH ?>css/dashboard.css">
    <link rel="stylesheet" href="<?= URL_PATH ?>css/usuarios.css">
    <link rel="stylesheet" href="<?= URL_PATH ?>css/configuracion.css">
    <link rel="stylesheet" href="<?= URL_PATH ?>css/evaluacion.css">
    <link rel="stylesheet" href="<?= URL_PATH ?>css/notificaciones.css">
    <link rel="stylesheet" href="<?= URL_PATH ?>css/auth.css">
    <link rel="stylesheet" href="<?= URL_PATH ?>css/modals.css">
    <link rel="stylesheet" href="<?= URL_PATH ?>css/roles.css">
    <link rel="stylesheet" href="<?= URL_PATH ?>css/permissions.css">
    <link rel="stylesheet" href="<?= URL_PATH ?>css/gestorDocumental.css">
    <link rel="stylesheet" href="<?= URL_PATH ?>css/planmejoras.css">
    <link rel="stylesheet" href="<?= URL_PATH ?>css/reportes.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster/dist/MarkerCluster.css" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster/dist/MarkerCluster.Default.css" />
    <link rel="icon" href="<?= URL_PATH ?>assets/img/logoSuperarse.png" type="image/png">

    <!-- Font Awesome para íconos -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <!-- JavaScript para Modales -->
    <script src="<?= URL_PATH ?>js/modals.js"></script>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>

<body>

    <?php require BASE_PATH . '/app/views/layout/sidebar.php'; ?>

    <div class="content">