<?php

include 'config.php';
include 'autoloader.php';
include 'functions.php';

include_once '../orm/load.php';

use ORM\Orm;

use App\Helpers\InitDatabase;

$ds = DIRECTORY_SEPARATOR;

$orm = Orm::getInstance();
$orm->setConnection('Store-SQLite', [
	'namespace' => 'App\\Models\\Store',
	'modelsFolder' => __DIR__ . $ds . 'models' . $ds . 'store',
// 	'create' => true,
// 	'drop' => true,
// 	'beforeDrop' => [new InitDatabase, 'beforeDrop'],
// 	'afterCreate' => [new InitDatabase, 'afterCreate'],
]);

$em = $orm->createEntityManager();
$query = $em->createQuery(\App\Models\Store\Staff::class);
$query->join(\App\Models\Store\Staff::class, 'sup', \ORM\Constants\JoinTypes::LEFT);
// $query->where('s.name')->equals('Diego');
// $query->where('sup.name')->equals('Diego');
// $query->where('s.name')->beginsWith('A');
// $query->where('s.name')->contains('i');
$staffs = $query->list();

// vd($query);
// $staffs = $em->list(\App\Models\Store\Staff::class, 2);
foreach($staffs as $staff) {
	vd($staff->name . ' is ' . (empty($staff->supervisor) ? '' : 'not ') . 'a supervisor');
}

// $staff = $em->find(\App\Models\Store\Staff::class, 2);
// vd($staff->name);
// $supervisor = $staff->supervisor;
// vd($supervisor->name);

// foreach($supervisor->supervisees as $supervisee) {
// 	vd($supervisee->name);
// }
