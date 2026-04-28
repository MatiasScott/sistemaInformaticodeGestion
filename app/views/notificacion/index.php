<?php
/**
 * @var array $notificaciones
 * @var bool $canViewAll
 */

function formatNotificationDate($fecha)
{
    if (empty($fecha)) {
        return 'Fecha no disponible';
    }

    try {
        return (new DateTime($fecha))->format('d/m/Y H:i');
    } catch (Exception $e) {
        return (string)$fecha;
    }
}

function resolveNotificationActionLabel($mensaje)
{
    $mensaje = strtolower((string)$mensaje);
    if (strpos($mensaje, 'eliminó') !== false) {
        return 'Eliminación';
    }
    if (strpos($mensaje, 'actualizó') !== false) {
        return 'Actualización';
    }
    if (strpos($mensaje, 'creó') !== false) {
        return 'Creación';
    }

    return 'Actividad';
}

$notificaciones = $notificaciones ?? [];
$filtroActual = isset($_GET['filtro']) ? strtolower(trim((string)$_GET['filtro'])) : 'todas';
$filtrosValidos = ['todas', 'creacion', 'actualizacion', 'eliminacion', 'no_leidas'];

if (!in_array($filtroActual, $filtrosValidos, true)) {
    $filtroActual = 'todas';
}

$totalNotificaciones = count($notificaciones);
$unreadCount = 0;

foreach ($notificaciones as $item) {
    if (!isset($item['leido']) || (int)$item['leido'] !== 1) {
        $unreadCount++;
    }
}

$notificacionesFiltradas = array_values(array_filter($notificaciones, function ($item) use ($filtroActual) {
    $leido = isset($item['leido']) ? (int)$item['leido'] === 1 : (($item['estado'] ?? 'no_leido') === 'leido');
    $label = strtolower(resolveNotificationActionLabel($item['mensaje'] ?? ''));

    if ($filtroActual === 'no_leidas') {
        return !$leido;
    }

    if ($filtroActual === 'creacion') {
        return $label === 'creación';
    }

    if ($filtroActual === 'actualizacion') {
        return $label === 'actualización';
    }

    if ($filtroActual === 'eliminacion') {
        return $label === 'eliminación';
    }

    return true;
}));

$filtros = [
    'todas' => 'Todas',
    'creacion' => 'Creaciones',
    'actualizacion' => 'Actualizaciones',
    'eliminacion' => 'Eliminaciones',
    'no_leidas' => 'No leídas',
];
?>
<?php require BASE_PATH . '/app/views/layout/header.php'; ?>

<div class="card">

    <div class="notifications-toolbar mb-3">
        <div>
            <h2>
                <i class="fas fa-bell"></i> Notificaciones
            </h2>
            <p class="notifications-subtitle">
                <?= !empty($canViewAll) ? 'Vista global de actividad del sistema.' : 'Actividad relacionada con tus módulos y tus propias acciones.' ?>
            </p>
        </div>

        <div class="notifications-summary">
            <div class="notifications-summary-card">
                <span class="notifications-summary-label">Total</span>
                <strong><?= $totalNotificaciones ?></strong>
            </div>
            <div class="notifications-summary-card notifications-summary-card-accent">
                <span class="notifications-summary-label">No leídas</span>
                <strong><?= $unreadCount ?></strong>
            </div>
        </div>
    </div>

    <div class="notification-toolbar-actions mb-3">
        <div class="notification-filters">
            <?php foreach ($filtros as $key => $label): ?>
                <a href="<?= URL_PATH ?>notificacion?filtro=<?= urlencode($key) ?>"
                    class="filter-btn <?= $filtroActual === $key ? 'active' : '' ?>">
                    <?= htmlspecialchars($label) ?>
                </a>
            <?php endforeach; ?>
        </div>

        <?php if ($unreadCount > 0): ?>
            <a href="<?= URL_PATH ?>notificacion/marcarTodasLeidas"
                class="btn btn-secondary notification-mark-all-btn"
                onclick="return confirm('¿Marcar todas las notificaciones como leídas?')">
                <i class="fas fa-check-double"></i> Marcar todas como leídas
            </a>
        <?php endif; ?>
    </div>

    <?php if (empty($notificacionesFiltradas)): ?>

        <div class="alert alert-info">
            <i class="fas fa-info-circle"></i>
            No hay notificaciones para el filtro seleccionado.
        </div>

    <?php else: ?>

        <div class="notification-list">

            <?php foreach ($notificacionesFiltradas as $n): ?>

                <?php
                $leido = isset($n['leido']) ? (int)$n['leido'] === 1 : (($n['estado'] ?? 'no_leido') === 'leido');
                $mensaje = htmlspecialchars($n['mensaje'] ?? $n['titulo'] ?? '');
                $fecha = $n['created_at'] ?? $n['fecha'] ?? '';
                $actionLabel = resolveNotificationActionLabel($n['mensaje'] ?? '');
                ?>

                <div class="notification-item <?= !$leido ? 'no-leida' : '' ?>">

                    <div class="notification-content">
                        <div class="notification-topline">
                            <span class="notification-type-badge"><?= htmlspecialchars($actionLabel) ?></span>
                            <span class="notification-date">
                                <i class="fas fa-clock"></i>
                                <?= htmlspecialchars(formatNotificationDate($fecha)) ?>
                            </span>
                        </div>

                        <div class="notification-message">
                            <?= $mensaje ?>
                        </div>

                        <div class="notification-meta">
                            <?php if ($leido): ?>
                                <span class="badge badge-success">
                                    <i class="fas fa-check-circle"></i> Leído
                                </span>
                            <?php else: ?>
                                <span class="badge badge-warning">
                                    <i class="fas fa-envelope"></i> No leído
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="notification-actions">
                        <?php if (!$leido): ?>
                            <a href="<?= URL_PATH ?>notificacion/marcarLeido/<?= $n['id'] ?>"
                                class="btn btn-sm btn-primary">
                                <i class="fas fa-check"></i> Marcar como leído
                            </a>
                        <?php endif; ?>
                    </div>

                </div>

            <?php endforeach; ?>

        </div>

    <?php endif; ?>

</div>

<?php require BASE_PATH . '/app/views/layout/footer.php'; ?>