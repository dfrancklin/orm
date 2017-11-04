<?php

include 'config.php';
include 'autoloader.php';
include 'functions.php';

include_once '../orm/load.php';

use ORM\Orm;

$ds = DIRECTORY_SEPARATOR;

$orm = Orm::getInstance();
$orm->setConnection('RFID-SQLite', [
	'namespace' => 'App\\Models\\RFID',
	'modelsFolder' => __DIR__ . $ds . 'models' . $ds . 'RFID',
	'create' => true,
	'drop' => true
]);

$orm->createEntityManager()->find(\App\Models\RFID\Aluno::class, 1);

// $orm->setConnection('Sakila');
// $orm->setConnection('RFID', [
// 	'namespace' => 'App\\Models\\RFID',
// 	'modelsFolder' => __DIR__ . $ds . 'models' . $ds . 'RFID',
// 	'create' => true,
// 	'drop' => true
// ]);

