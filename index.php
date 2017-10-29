<?php

$refresh = true;

include 'config.php';
include 'autoloader.php';
include 'functions.php';

use ORM\Orm;

use App\Models\GreeningU\Usuario;
use App\Models\GreeningU\Comunidade;
use App\Models\GreeningU\Post;

include_once 'orm/load.php';

$orm = Orm::getInstance();
$orm->addConnection('GreeningU');
$orm->addConnection('default');
$em = $orm->createEntityManager('GreeningU');
$em2 = $orm->createEntityManager('default');

$usuario = $em->find(Usuario::class, 1);

try {
	$em->beginTransaction();
	$rs = $em->save($usuario);
	// $em->commit();
	$em->rollback();

	vd($rs->__getObject());
} catch (Exception $e) {
	$em->rollback();
	throw $e;
}
