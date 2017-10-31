<?php

include 'config.php';
include 'autoloader.php';
include 'functions.php';

use ORM\Orm;

use App\Models\GreeningU\Usuario;
use App\Models\GreeningU\Comunidade;
use App\Models\GreeningU\Post;

include_once 'orm/load.php';

$orm = Orm::getInstance();
$orm->setConnection('GreeningU', true, true);
