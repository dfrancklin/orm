<?php

include 'config.php';
include 'autoloader.php';
include 'functions.php';

use ORM\Orm;

use App\Models\GreeningU\Usuario;

include_once 'orm/load.php';

$orm = Orm::getInstance();
$em = $orm->createEntityManager('GreeningU');

$object = new Usuario();

$object->id = 10;
$object->nome;
$object->sobrenome;
$object->email;
$object->login;
$object->senha;
$object->sexo;
$object->pontuacao;
$object->assinaturas;
$object->comunidades;
$object->posts;
$object->comentarios;
$object->votos;

$rs = $em->beginTransaction($object);

try {
	$rs = $em->save($object);
	$em->commit();
} catch (Exception $e) {
	$em->rollback();
}
