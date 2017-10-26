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

$usuario->id = 10;
$usuario->nome = 'Bridges';
$usuario->sobrenome = 'Ferrell';
$usuario->email = 'bridgesferrell@stucco.com';
$usuario->login = 'magna';
$usuario->senha = 'voluptate';
$usuario->sexo = 'male';
$usuario->pontuacao = 2233;

$rs = $em->beginTransaction($usuario);

try {
	$rs = $em->save($usuario);
	$em->commit();
} catch (Exception $e) {
	$em->rollback();
}
