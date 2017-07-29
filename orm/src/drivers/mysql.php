<?php

use ORM\Core\Driver;

Driver::$SCAPE_CHAR = '\\';
Driver::$TYPES = [
	'string' => 'VARCHAR(%d)',
	'int' => 'INTEGER',
];
