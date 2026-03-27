<!DOCTYPE html>
<html>

<head>
    <title>Login - Sistema Informático de Gestión</title>
    <!-- CSS Global y Auth -->
    <link rel="stylesheet" href="<?= URL_PATH ?>css/global.css">
    <link rel="stylesheet" href="<?= URL_PATH ?>css/auth.css">
    <link rel="icon" href="<?= URL_PATH ?>assets/img/logoSuperarse.png" type="image/png">

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>

<body class="auth-body">
    <div class="auth-container">
        <div class="login-box">
            <div class="login-header">
                <div class="login-logo">📊</div>
                <h1 class="login-title">Sistema Informático de Gestión</h1>
                <p class="login-subtitle">Ingresa tus credenciales para continuar</p>
            </div>

            <?php if (isset($error) && $error): ?>
                <div class="error-message">
                    ⚠️ <?= $error ?>
                </div>
            <?php endif; ?>

            <form method="POST" class="login-form">
                <div class="form-group">
                    <label for="email">📧 Correo Electrónico</label>
                    <input
                        type="email"
                        id="email"
                        name="email"
                        placeholder="usuario@ejemplo.com"
                        required
                        autofocus>
                </div>

                <div class="form-group">
                    <label for="password">🔒 Contraseña</label>
                    <input
                        type="password"
                        id="password"
                        name="password"
                        placeholder="Ingresa tu contraseña"
                        required>
                </div>

                <button type="submit" class="login-button">Iniciar Sesión</button>
            </form>

            <?php if (!empty($_GET['timeout'])): ?>
                <div class="modal-overlay active">
                    <div class="modal modal-error">
                        <div class="modal-header">
                            <h2>⏳ Sesión Expirada</h2>
                        </div>
                        <div class="modal-body">
                            <p>Tu sesión se cerró por inactividad. Serás redirigido para iniciar sesión nuevamente.</p>
                        </div>
                        <div class="modal-footer">
                            <a href="<?= URL_PATH ?>auth/login" class="btn btn-primary">Ir al login</a>
                        </div>
                    </div>
                </div>
                <script>
                    setTimeout(function() {
                        window.location.href = '<?= URL_PATH ?>auth/login';
                    }, 3000);
                </script>
            <?php endif; ?>

            <div class="login-footer">
                © 2026 Instituto Superarse. - Todos los derechos reservados
            </div>
        </div>
    </div>

</body>

</html>