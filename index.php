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

echo '<h1>Usuários (' . count($usuarios) . ')</h1><hr>';

foreach($usuarios as $usuario) {
	echo '<h2>' . $usuario->nome . ' ' . $usuario->sobrenome . '</h2>';

	pr('++++++++++++++++++++++++++++');

	if (!count($usuario->assinaturas)) {
		echo '<h3>Usuário não possiu assinaturas ainda</h3>';
	} else {
		echo '<h3>Assinaturas do usuário (' . count($usuario->assinaturas) . ')</h3>';

		foreach($usuario->assinaturas  as $comunidade) {
			pr('<strong>' . $comunidade->nome . '</strong> do líder <strong>' . $comunidade->lider->nome . ' ' . $comunidade->lider->sobrenome . '</strong>');
		}
	}

	pr('-----------------------------------------');

	if (!count($usuario->comunidades)) {
		echo '<h3>Usuário não possiu comunidades ainda</h3>';
	} else {
		echo '<h3>Comunidades do usuário (' . count($usuario->comunidades) . ')</h3>';

		foreach($usuario->comunidades  as $comunidade) {
			echo '<h4>' . $comunidade->nome . '</h4>';

			if (!count($comunidade->posts)) {
				echo '<h5>Comunidade não possiu posts ainda</h5>';
			} else {
				echo '<h5>Posts da comunidade "' . $comunidade->nome . '" (' . count($comunidade->posts) . ')</h5>';

				foreach($comunidade->posts  as $post) {
					pr('<strong>' . $post->titulo . '</strong> do usuário <strong>' . $post->usuario->nome . ' ' . $post->usuario->sobrenome . '</strong>');
				}
			}

			pr('~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~');

			if (!count($comunidade->usuarios)) {
				echo '<h5>Comunidade não possiu usuários assinantes ainda</h5>';
			} else {
				echo '<h5>Usuários assinantes da comunidade "' . $comunidade->nome . '" (' . count($comunidade->usuarios) . ')</h5>';

				foreach($comunidade->usuarios  as $assinante) {
					pr('<strong>' . $assinante->nome . ' ' . $assinante->sobrenome . '</strong>');
				}
			}
		}
	}

	pr('-----------------------------------------');

	if (!count($usuario->posts)) {
		echo '<h3>Usuário não possiu posts ainda</h3>';
	} else {
		echo '<h3>Posts do usuário (' . count($usuario->posts) . ')</h3>';

		foreach($usuario->posts  as $post) {
			pr('<strong>' . $post->titulo . '</strong> na comunidade <strong>' . $post->comunidade->nome . '</strong>');
		}
	}

	pr('##########################################################################');
}
