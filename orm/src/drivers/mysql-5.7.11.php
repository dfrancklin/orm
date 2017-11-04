<?php

use ORM\Core\Driver;

if (!class_exists('MySQLDriver_5_7_11')) {

	class MySQLDriver_5_7_11 extends Driver
	{

		const NAME = 'MySQL';
		const VERSION = '5.7.11';

		public function __construct()
		{
			$this->GENERATE_ID_TYPE = 'ATTR';
			$this->GENERATE_ID_ATTR = 'AUTO_INCREMENT';
			$this->SUPPORTS_IF_EXISTS = true;
			$this->PAGE_TEMPLATE = '%s ' . "\n" . ' LIMIT %d, %d';
			$this->TOP_TEMPLATE = '%s ' . "\n" . ' LIMIT %d';
			$this->DATA_TYPES = [
				'string' => 'VARCHAR(%d)',
				'int' => 'INTEGER',
				'float' => 'DOUBLE',
				'lob' => 'CLOB',
				'date' => 'DATE',
				'time' => 'TIME',
				'datetime' => 'DATETIME',
			];
		}

	}

}

return $driver = new MySQLDriver_5_7_11;
