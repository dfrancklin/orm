<?php

namespace ORM\Builders;

trait Having {

	private $havingConditions;

	public function having($property) {
		$this->chain = 'having';

		vd($property);

		return $this;
	}

}
