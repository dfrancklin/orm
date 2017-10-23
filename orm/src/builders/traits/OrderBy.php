<?php

namespace ORM\Builders\Traits;

trait OrderBy {

	public static $ASC = 'ASC', $DESC = 'DESC';

	private $orders;

	public function orderBy($order, $dir=null) {
		if (!$dir || ($dir !== self::$ASC && self::$DESC)) {
			$dir = self::$ASC;
		}

		array_push($this->orders, [$order, $dir]);

		return $this;
	}

	private function resolveOrderBy() {
		$resolved = [];
		$sql = '';

		if (!empty($this->orders)) {
			$sql = "\n\t" . ' ORDER BY ';
		}

		foreach ($this->orders as $order) {
			list($property, $dir) = $order;
			list($prop) = $this->processProperty($property);

			array_push($resolved, sprintf('%s %s', $prop, $dir));
		}

		return $sql . join(', ', $resolved);
	}

}
