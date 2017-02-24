<meta http-equiv="refresh" content="3">
<?php
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

use App\Models\GreeningU\Usuario;

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

$query = Orm::query('RFID');
$rs = $query->from(Aluno::class)->all();
echo '<br>';

$query = Orm::query('RFID');
$rs = $query->from(Log::class)->joins([Aluno::class, Ambiente::class])->all();
echo '<br><br>';


$query = Orm::query('GreeningU');
$rs = $query->from(Usuario::class)->all();
echo '<br><br>';


//OK 
$query = Orm::query();
$query->from(Client::class)->all();
echo '<br>';

$query = Orm::query();
$query->from(Client::class)->joins([Order::class, ItemOrder::class, Product::class])->all();
echo '<br>';

$query = Orm::query();
$query->from(Order::class)->joins([Client::class])->all();

// $query->from(Client::class)->joins([Order::class, ItemOrder::class, Product::class])->all();
//var_dump($query);

// $client = Orm::getShadow(Client::class);
// var_dump($client);

// $order = Orm::getShadow(Order::class);
// var_dump($order);

// $itemOrder = Orm::getShadow(ItemOrder::class);
// var_dump($itemOrder);

// $product = Orm::getShadow(Product::class);
// var_dump($product);
