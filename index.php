<?php

include 'config.php';
include 'autoloader.php';
include 'functions.php';

use ORM\Orm;

use App\Models\GreeningU\Usuario;

include_once 'orm/load.php';

$orm = Orm::getInstance();
$em = $orm->createEntityManager('GreeningU');

$usuario = new Usuario();

$usuario->id = 3;
$usuario->nome = 'Bridges';
$usuario->sobrenome = 'Ferrell';
$usuario->email = 'bridgesferrell@stucco.com';
$usuario->login = 'magna';
$usuario->senha = 'voluptate';
$usuario->sexo = 'h';
$usuario->pontuacao = 2233;

$post = new \App\Models\GreeningU\Post;
$post->usuario = $usuario;

$rs = $em->beginTransaction();

try {
	$rs = $em->save($post);
	// $rs = $em->save($usuario);
	$em->commit();
} catch (Exception $e) {
	throw $e;
	$em->rollback();
}
