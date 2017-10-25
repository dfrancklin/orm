<?php

use ORM\Core\Driver;

Driver::$SCAPE_CHAR = '\\';
Driver::$PAGE_TEMPLATE = '%s ' . "\n\t" . ' LIMIT %d, %d';
Driver::$TOP_TEMPLATE = '%s ' . "\n\t" . ' LIMIT %d';
Driver::$TYPES = [
	'string' => 'VARCHAR(%d)',
	'int' => 'INTEGER',
	'float' => 'DOUBLE',
];
