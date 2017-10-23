<?php

use ORM\Core\Driver;

Driver::$SCAPE_CHAR = '\\';
Driver::$TYPES = [
	'string' => 'VARCHAR(%d)',
	'int' => 'INTEGER',
];
Driver::$PAGE_TEMPLATE = '%s LIMIT %d, %d';
Driver::$TOP_TEMPLATE = '%s LIMIT %d';
