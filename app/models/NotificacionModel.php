<?php

require_once BASE_PATH . '/app/core/Model.php';

class NotificacionModel extends Model
{

    protected $table = 'notificaciones';

    public function create($data)
    {
        return $this->insert($data);
    }

    public function marcarLeido($id)
    {
        return $this->update($id, ['leido' => 1]);
    }
}
