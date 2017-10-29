<?php

use ORM\Core\Driver;

Driver::$GENERATE_ID_TYPE = 'ATTR';
Driver::$GENERATE_ID_QUERY = '';
Driver::$SCAPE_CHAR = '\\';
Driver::$PAGE_TEMPLATE = '%s ' . "\n" . ' LIMIT %d, %d';
Driver::$TOP_TEMPLATE = '%s ' . "\n" . ' LIMIT %d';
Driver::$DATA_TYPES = [
	'string' => 'VARCHAR(%d)',
	'int' => 'INTEGER',
	'float' => 'DOUBLE',
];
