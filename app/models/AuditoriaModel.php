<?php

require_once BASE_PATH . '/app/core/Model.php';

class AuditoriaModel extends Model
{

    protected $table = 'auditoria';

    public function registrar($data)
    {
        return $this->insert($data);
    }
}
