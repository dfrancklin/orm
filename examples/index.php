<?php

include 'config.php';
include 'autoloader.php';
include 'functions.php';

include_once '../orm/load.php';

use ORM\Orm;

use App\Models\RFID\Aluno;

$ds = DIRECTORY_SEPARATOR;

$orm = Orm::getInstance();
$orm->setConnection('RFID');

$em = $orm->createEntityManager();

$em->find(Aluno::class, 1);

$query = $em->createQuery();
$query->from(Aluno::class)->top(3)->list();

// $orm->setConnection('RFID-SQLite', [
// 	'namespace' => 'App\\Models\\RFID',
// 	'modelsFolder' => __DIR__ . $ds . 'models' . $ds . 'RFID',
// 	'create' => true,
// 	'drop' => true
// ]);

// $orm->setConnection('Sakila');
// $orm->setConnection('RFID', [
// 	'namespace' => 'App\\Models\\RFID',
// 	'modelsFolder' => __DIR__ . $ds . 'models' . $ds . 'RFID',
// 	'create' => true,
// 	'drop' => true
// ]);

