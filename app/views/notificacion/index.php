<?php require BASE_PATH . '/app/views/layout/header.php'; ?>

<div class="card">

    <div class="d-flex justify-between align-center mb-3">
        <h2>
            <i class="fas fa-bell"></i> Notificaciones
        </h2>
    </div>

    <?php $notificaciones = $notificaciones ?? []; ?>

    <?php if (empty($notificaciones)): ?>

        <div class="alert alert-info">
            <i class="fas fa-info-circle"></i>
            No hay notificaciones disponibles.
        </div>

    <?php else: ?>

        <div class="notification-list">

            <?php foreach ($notificaciones as $n): ?>

                <?php
                $estado = $n['estado'] ?? 'no_leido';
                $mensaje = htmlspecialchars($n['mensaje'] ?? $n['titulo'] ?? '');
                $fecha = $n['created_at'] ?? $n['fecha'] ?? '';
                ?>

                <div class="notification-item <?= $estado !== 'leido' ? 'no-leida' : '' ?>">

                    <div class="notification-content">
                        <div class="notification-message">
                            <?= $mensaje ?>
                        </div>

                        <div class="notification-meta">
                            <span class="notification-date">
                                <i class="fas fa-clock"></i>
                                <?= htmlspecialchars($fecha) ?>
                            </span>

                            <?php if ($estado === 'leido'): ?>
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
                        <?php if ($estado !== 'leido'): ?>
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