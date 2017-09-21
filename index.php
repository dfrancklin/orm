<?php

// echo phpinfo(); die();
header('refresh:2');

// foreach(timezone_abbreviations_list() as $t): vd($t); endforeach; die();
// timezone_identifiers_list;
// America/Sao_Paulo

spl_autoload_register(function ($class) {
	$root = 'App';

	if (substr($class, 0, strlen($root)) !== $root) {
		return;
	}

	$class = substr_replace($class, '', 0, strlen($root));
	$class = __DIR__ . DIRECTORY_SEPARATOR . $class . '.php';

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

// echo 'GreeningU';
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
		->where('v.data')->bt(new DateTime(), new DateTime())
			->or('v.data')->between(new DateTime(), new DateTime())
			->and('v.data')->nbt(new DateTime(), new DateTime())
			->or('v.data')->notBetween(new DateTime(), new DateTime())
			->and('v.data')->isn()
			->or('v.data')->isNull()
			->and('v.data')->isnn()
			->or('v.data')->isNotNull()
			->and('u.nome')->eq('Aline')
			->or('u.nome')->equals('Aline')
			->and('u.nome')->neq('Diego')
			->or('u.nome')->notEquals('Diego')
			->and('u.id')->in(1, 2, 3)
			->or('u.id')->notIn(4, 5, 6)

			->and('p.data')->gt(new DateTime())
			->or('p.data')->greaterThan(new DateTime())
			->and('p.data')->lt(new DateTime())
			->or('p.data')->lessThan(new DateTime())
			->and('p.data')->goet(new DateTime())
			->or('p.data')->greaterOrEqualsThan(new DateTime())
			->and('p.data')->loet(new DateTime())
			->or('p.data')->lessOrEqualsThan(new DateTime())

			->and('u.nome')->lk('_line%')
			->or('u.nome')->like('_line%')
			->and('u.nome')->ctn('Alexandrino')
			->or('u.nome')->contains('Alexandrino')
			->and('u.nome')->nctn('Alexandrino')
			->or('u.nome')->notContains('Alexandrino')
			->and('u.nome')->bwt('Aline')
			->or('u.nome')->beginsWith('Aline')
			->and('u.nome')->nbwt('Aline')
			->or('u.nome')->notBeginsWith('Aline')
			->and('u.nome')->ewt('Diego')
			->or('u.nome')->endsWith('Diego')
			->and('u.nome')->newt('Diego')
			->or('u.nome')->notEndsWith('Diego')
		->having()->avg('u.pontuacao')->greaterThan(100)
			->or()->avg('u.pontuacao')->lessThan(200)
			->or()->avg('u.pontuacao')->between(100, 200)
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
