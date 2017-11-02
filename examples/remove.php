<?php

include 'config.php';
include 'autoloader.php';
include 'functions.php';
include 'load.php';

$orm->setConnection('GreeningU');
$em = $orm->createEntityManager('GreeningU');

$usuario = $em->find(Usuario::class, 1);

try {
	$em->beginTransaction();
	$rows = $em->remove($usuario);
	$em->rollback();

	vd($rows);
} catch (Exception $e) {
	$em->rollback();
	throw $e;
}
