<?php
namespace ORM\Core;

class Driver {

	public static
		$GENERATE_ID_TYPE,
		$GENERATE_ID_QUERY,
		$SCAPE_CHAR,
		$PAGE_TEMPLATE,
		$TOP_TEMPLATE,
		$DATA_TYPES = [],
		$FORMATS = [
			'date' => 'Y-m-d',
			'time' => 'H:i:s',
			'datetime' => 'Y-m-d H:i:s'
		];

}
