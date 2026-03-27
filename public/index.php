<?php

require_once '../app/config/config.php';
require_once '../app/config/database.php';
require_once '../app/config/session.php';
require_once '../app/core/Router.php';

$router = new Router();
$router->run();
