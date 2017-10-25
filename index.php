<?php

header('refresh:2');

error_reporting(E_ALL);
ini_set('display_errors', 1);

spl_autoload_register(function ($class) {
	$root = 'App';
	$srcFolder = '';

	if (substr($class, 0, strlen($root)) !== $root) {
		return;
	}

    $classInfo = explode('\\', $class);
    $className = array_pop($classInfo);
    $namespace = strtolower(implode(DIRECTORY_SEPARATOR, $classInfo));
	$folder = substr_replace($namespace, $srcFolder, 0, strlen($root));
	$file = __DIR__ . DIRECTORY_SEPARATOR . $folder . DIRECTORY_SEPARATOR . $className . '.php';
    
	if (file_exists($file)) {
		include $file;
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
// $orm->setConnection('GreeningU');
$query = $orm->createQuery();
$usuarios = $query->from(Usuario::class, 'u')->all();

pr('Usuários (' . count($usuarios) . ')');
pr('================');

foreach($usuarios as $usuario) {
	pr($usuario->nome . ' ' . $usuario->sobrenome);

	if (!count($usuario->comunidades)) {
		pr('Usuário não possiu comunidades ainda');
	} else {
		pr('Comunidades do usuário (' . count($usuario->comunidades) . ')');

		foreach($usuario->comunidades  as $comunidade) {
			pr($comunidade->nome);

			pr('-------------------------------');

			if (!count($comunidade->posts)) {
				pr('Comunidade não possiu posts ainda');
			} else {
				pr('Posts da comunidade (' . count($comunidade->posts) . ')');

				foreach($comunidade->posts  as $post) {
					pr($post->titulo . ' do usuário ' . $post->usuario->nome . ' ' . $post->usuario->sobrenome);
				}
			}
		}
	}

	pr('-------------------------------');

	if (!count($usuario->posts)) {
		pr('Usuário não possiu posts ainda');
	} else {
		pr('Posts do usuário (' . count($usuario->posts) . ')');

		foreach($usuario->posts  as $post) {
			pr($post->titulo . ' na comunidade ' . $post->comunidade->nome);
		}
	}

	pr('##################################################');
}
