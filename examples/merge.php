<?php

include 'config.php';
include 'autoloader.php';
include 'functions.php';
include 'load.php';

$orm->addConnection('GreeningU');
$orm->addConnection('default');
$em = $orm->createEntityManager('GreeningU');
$em2 = $orm->createEntityManager('default');

$usuario = $em->find(Usuario::class, 1);
$comunidade = $em->find(Comunidade::class, 1);

try {
	$em->beginTransaction();

	$usuario->email = strtolower($usuario->nome . $usuario->sobrenome) . '@email.com';
	$usuario->login = '';
	$usuario->pontuacao = 0;
	$usuario->assinaturas;
	$usuario->posts;
	$usuario = $em->save($usuario);
	vd($usuario->__getObject());

	// $comunidade = $em->save($comunidade);
	// $em->commit();
	$em->rollback();

	vd($comunidade->__getObject());
} catch (Exception $e) {
	$em->rollback();
	throw $e;
}
