<?php

// echo phpinfo(); die();
header("refresh:2");

// foreach(timezone_abbreviations_list() as $t): vd($t); endforeach; die();
// timezone_identifiers_list;
// America/Sao_Paulo

spl_autoload_register(function ($class) {
	if (substr($class, 0, 3) !== 'App') {
		return;
	}

	$class = str_replace('App\\', '', $class);
	$class = __DIR__ . '\\' . $class . '.php';

	if (file_exists($class)) {
		include $class;
	}
});

function vd($v) {
	echo '<pre style="white-space: pre-wrap; word-break: break-all;">';
	var_dump($v);
	echo '</pre>';
}

function pr($v) {
	echo '<pre style="white-space: pre-wrap; word-break: break-all;">';
	print_r($v);
	echo '</pre>';
}

use ORM\Orm;
use ORM\Builders\Query;

use App\Models\GreeningU\Post;
use App\Models\GreeningU\Voto;
use App\Models\GreeningU\Usuario;
use App\Models\GreeningU\Comunidade;
use App\Models\GreeningU\Comentario;

use App\Models\RFID\Aluno;
use App\Models\RFID\Ambiente;
use App\Models\RFID\Log;
use App\Models\RFID\Responsavel;

use App\Models\Store\Client;
use App\Models\Store\Order;
use App\Models\Store\Product;
use App\Models\Store\ItemOrder;

include_once 'orm/load.php';

$orm = Orm::getInstance();
$orm->setConnection('default');
$orm->addConnection('RFID');
$orm->addConnection('GreeningU');

echo 'GreeningU';
$query = $orm->createQuery('GreeningU');
$rs = $query
		->distinct(true)
		->from(Usuario::class, 'u')
		->joins([
			[Voto::class, 'v'],
			[Post::class, 'p'],
			[Comentario::class, 'ct'],
			[Comunidade::class, 'cm'],
		])
		->where("v.data")->between(new DateTime(), new DateTime())
		->where("v.data")->isNull()->and("v.data")->isNotNull()
		->where("u.nome")->equals("Aline")->and('u.nome')->notEquals('Diego')
		->where("u.nome")->like("Aline%")->and('u.nome')->notLike('Diego%')

$query = Orm::query('GreeningU');
$rs = $query
		->from(Post::class)
		->joins([
			Voto::class,
			Usuario::class,
			Comentario::class,
			Comunidade::class,
		])
		->all();

$query = Orm::query('GreeningU');
$rs = $query
		->distinct(true)
		->from(Comunidade::class)
		->joins([Post::class, Voto::class, Usuario::class])
		->all();

// $query = $orm->createQuery('GreeningU');
// $rs = $query
// 		->from(Usuario::class, 'u')
// 		->join(Voto::class, 'v')
// 		->join(Post::class, 'p')
// 		->join(Comentario::class, 'ct')
// 		->join(Comunidade::class, 'cm')
// 		->all();

// $query = $orm->createQuery('GreeningU');
// $rs = $query
// 		->from(Post::class, 'p')
// 		->joins([
// 			Voto::class,
// 			Usuario::class,
// 			Comentario::class,
// 			Comunidade::class,
// 		])
// 		->all();

// $query = $orm->createQuery('GreeningU');
// $rs = $query
// 		->distinct(true)
// 		->from(Comunidade::class)
// 		->joins([Post::class, Voto::class, Usuario::class])
// 		->all();

// $query = $orm->createQuery('GreeningU');
// $rs = $query->from(Comunidade::class)->all();

// $query = $orm->createQuery('GreeningU');
// $rs = $query->from(Comunidade::class)->joins([Post::class, Voto::class])->all();

// $query = $orm->createQuery('GreeningU');
// $rs = $query->from(Comunidade::class)->joins([Post::class])->all();

// $query = $orm->createQuery('GreeningU');
// $rs = $query->from(Comunidade::class)->all();

// $query = $orm->createQuery('GreeningU');
// $rs = $query->from(Post::class)->joins([Voto::class, Usuario::class])->all();

// $query = $orm->createQuery('GreeningU');
// $rs = $query->from(Post::class)->joins([Voto::class])->all();

// $query = $orm->createQuery('GreeningU');
// $rs = $query->from(Post::class)->joins([Usuario::class])->all();

// $query = $orm->createQuery('GreeningU');
// $rs = $query->from(Usuario::class)->joins([Voto::class])->all();

// $query = $orm->createQuery('GreeningU');
// $rs = $query->from(Usuario::class)->all();
// echo '<br><br>';

// echo 'RFID';
// $query = $orm->createQuery('RFID');
// $rs = $query->from(Responsavel::class)->all();

// $query = $orm->createQuery('RFID');
// $rs = $query->from(Aluno::class)->all();

// $query = $orm->createQuery('RFID');
// $rs = $query->from(Log::class)->joins([Responsavel::class, Ambiente::class, Aluno::class])->all();

// $query = $orm->createQuery('RFID');
// $rs = $query->from(Aluno::class)->joins([Responsavel::class])->all();
// echo '<br><br>';

// echo 'Store';
// $query = $orm->createQuery();
// $query->from(Client::class)->all();

// $query = $orm->createQuery();
// $query->from(Client::class)->joins([Order::class, ItemOrder::class, Product::class])->all();

// $query = $orm->createQuery();
// $query->from(Order::class)->joins([Client::class])->all();