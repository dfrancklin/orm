<?php

include 'config.php';
include 'autoloader.php';
include 'functions.php';

include_once '../orm/load.php';

use ORM\Orm;

use App\Models\GreeningU\Usuario;
use App\Models\GreeningU\Comunidade;
use App\Models\GreeningU\Post;

$ds = DIRECTORY_SEPARATOR;

$orm = Orm::getInstance();
$orm->setConnection('RFID', [
	'namespace' => 'App\\Models\\RFID',
	'modelsFolder' => __DIR__ . $ds . 'models' . $ds . 'RFID',
	'create' => true,
	'drop' => true
]);