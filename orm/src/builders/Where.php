<?php

namespace ORM\Builders;

trait Where {

	private $conditions;

	public function where($conditions = []) {
		$this->conditions = $conditions;

		return $this;
	}

}
