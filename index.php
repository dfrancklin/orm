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
$orm->setConnection('default');
$orm->addConnection('RFID');
$orm->addConnection('GreeningU');

$query = $orm->createQuery('GreeningU');
$rs = $query
		->distinct(true)
			->count('v.id', 'quantity')
			->avg('u.pontuacao')
			->min('u.pontuacao')
			->max('u.pontuacao')
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

		->groupBy('p.data', 'u.id')

		->having()->avg('u.pontuacao')->gt(100)
			->or()->avg('u.pontuacao')->lt(200)
			->and()->avg('u.pontuacao')->bt(100, 200)
			->and()->count('u.pontuacao')->gt(100)
			->and()->min('u.pontuacao')->gt(100)
			->and()->max('u.pontuacao')->lt(200)
			->and()->sum('u.pontuacao')->lt(300)

		->orderBy('p.data', Query::$ASC)

		->all();
