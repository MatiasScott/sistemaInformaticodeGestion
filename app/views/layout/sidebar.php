<div class="sidebar">

    <div class="sidebar-header">
        <div>
            <img src="<?= URL_PATH ?>assets/img/LOGO SUPERARSE PNG-02.png" alt="Logo" height="90">
        </div>
    </div>

    <ul class="sidebar-menu">

        <!-- Dashboard -->
        <li class="menu-item">
            <a href="<?= URL_PATH ?>dashboard">📊 Dashboard</a>
        </li>

        <?php
        $modules = require BASE_PATH . '/app/config/modules.php';

        $grupos = [
            'Administración' => ['user', 'role', 'permission', 'cargo'],
            'Gestión General' => ['periodo', 'escala-cualitativa', 'gestor-documental'],
            'Reportes' => ['reportes'],
            'Modelo de Evaluación' => ['criterio', 'subcriterio', 'indicador'],
            'Evaluaciones' => ['evaluacion', 'documento'],
            'Mejoramiento Continuo' => ['plan-mejora'],
            'Sistema' => ['notificacion']
        ];
        ?>

        <?php foreach ($grupos as $titulo => $items): ?>
            <li class="menu-title"><?= $titulo ?></li>

            <?php foreach ($items as $item): ?>
                <?php if (in_array($item, $modules)): ?>
                    <?php $menuLabel = $item === 'notificacion' ? 'Notificaciones' : ucfirst(str_replace('-', ' ', $item)); ?>
                    <li>
                        <a href="<?= URL_PATH . $item ?>" class="<?= $item === 'notificacion' ? 'sidebar-link-with-badge' : '' ?>">
                            <span><?= $menuLabel ?></span>
                            <?php if ($item === 'notificacion' && !empty($sidebarUnreadNotifications)): ?>
                                <span class="sidebar-notification-badge" aria-label="<?= (int)$sidebarUnreadNotifications ?> notificaciones no leídas">
                                    <?= (int)$sidebarUnreadNotifications ?>
                                </span>
                            <?php endif; ?>
                        </a>
                    </li>
                <?php endif; ?>
            <?php endforeach; ?>

        <?php endforeach; ?>

        <!-- Logout fijo -->
        <li class="menu-title">Cuenta</li>
        <li>
            <a href="<?= URL_PATH ?>auth/logout" class="logout-link">
                🚪 Cerrar Sesión
            </a>
        </li>

    </ul>

</div>