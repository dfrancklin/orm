<?php

include 'config.php';
include 'autoloader.php';
include 'functions.php';

include_once '../orm/load.php';

use ORM\Orm;

$ds = DIRECTORY_SEPARATOR;

$orm = Orm::getInstance();
$orm->setConnection('RFID-SQLite');
$em = $orm->createEntityManager();

$proxy = $em->find(\App\Models\RFID\Aluno::class, 1);
// $entity = $proxy->__getObject();
// vd($entity);
