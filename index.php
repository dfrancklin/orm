<?php
// echo phpinfo(); die();
header("refresh:2");
spl_autoload_register(function ($class) {
	if (substr($class, 0, 3) !== 'App') return;

	$class = str_replace('App\\', '', $class);
	$class = __DIR__ . '\\' . $class . '.php';

	if (file_exists($class)) {
		include $class;
	}
});

function vd($v) {
	echo '<pre>';
	var_dump($v);
	echo '</pre>';
}

function pr($v) {
	echo '<pre>';
	print_r($v);
	echo '</pre>';
}

use App\Models\GreeningU\Usuario;
use App\Models\GreeningU\Voto;
use App\Models\GreeningU\Post;
use App\Models\GreeningU\Comunidade;

use App\Models\RFID\Aluno;
use App\Models\RFID\Ambiente;
use App\Models\RFID\Log;
use App\Models\RFID\Responsavel;

use App\Models\Store\Client;
use App\Models\Store\Order;
use App\Models\Store\Product;
use App\Models\Store\ItemOrder;

use ORM\Orm;
use ORM\Core\Annotation;

include_once 'orm/load.php';

Orm::setConnection('default');
Orm::addConnection('RFID');
Orm::addConnection('GreeningU');

echo 'GreeningU';
$query = Orm::query('GreeningU');
$rs = $query
		->distinct(true)
		->from(Comunidade::class)
		->joins([Post::class, Voto::class, Usuario::class])
		->all();

$query = Orm::query('GreeningU');
$rs = $query->from(Comunidade::class)->joins([Post::class, Voto::class])->all();

$query = Orm::query('GreeningU');
$rs = $query->from(Comunidade::class)->joins([Post::class])->all();

$query = Orm::query('GreeningU');
$rs = $query->from(Comunidade::class)->all();

$query = Orm::query('GreeningU');
$rs = $query->from(Post::class)->joins([Voto::class, Usuario::class])->all();

$query = Orm::query('GreeningU');
$rs = $query->from(Post::class)->joins([Voto::class])->all();

$query = Orm::query('GreeningU');
$rs = $query->from(Post::class)->joins([Usuario::class])->all();

$query = Orm::query('GreeningU');
$rs = $query->from(Usuario::class)->joins([Voto::class])->all();

$query = Orm::query('GreeningU');
$rs = $query->from(Usuario::class)->all();
echo '<br><br>';

echo 'RFID';
$query = Orm::query('RFID');
$rs = $query->from(Responsavel::class)->all();

$query = Orm::query('RFID');
$rs = $query->from(Aluno::class)->all();

$query = Orm::query('RFID');
$rs = $query->from(Log::class)->joins([Responsavel::class, Ambiente::class, Aluno::class])->all();

$query = Orm::query('RFID');
$rs = $query->from(Aluno::class)->joins([Responsavel::class])->all();
echo '<br><br>';

echo 'Store';
$query = Orm::query();
$query->from(Client::class)->all();

$query = Orm::query();
$query->from(Client::class)->joins([Order::class, ItemOrder::class, Product::class])->all();

$query = Orm::query();
$query->from(Order::class)->joins([Client::class])->all();