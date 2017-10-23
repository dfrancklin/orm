<?php

header('refresh:2');

spl_autoload_register(function ($class) {
	$root = 'App';

	if (substr($class, 0, strlen($root)) !== $root) {
		return;
	}

	$class = substr_replace($class, '', 0, strlen($root));
	$class = __DIR__ . '/' . $class . '.php';

	if (file_exists($class)) {
		include $class;
	}
});

function vd(...$vs) {
	echo '<pre style="white-space: pre-wrap; word-break: break-all;">';
	foreach ($vs as $v) var_dump($v);
	echo '</pre>';
}

function pr(...$vs) {
	echo '<pre style="white-space: pre-wrap; word-break: break-all;">';
	foreach ($vs as $v) print_r($v);
	echo '</pre>';
}

use ORM\Orm;
use ORM\Builders\Query;

use App\Models\GreeningU\Post;
use App\Models\GreeningU\Voto;
use App\Models\GreeningU\Usuario;
use App\Models\GreeningU\Comunidade;
use App\Models\GreeningU\Comentario;

include_once 'orm/load.php';

$orm = Orm::getInstance();
$orm->setConnection('GreeningU');
$query = $orm->createQuery('GreeningU');
$rs = $query->from(Usuario::class, 'u')->page(1, 10)->all();
