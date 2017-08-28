<?php

namespace ORM\Builders;

trait Operator {

	private $chain;

	public function or(String $property = null) {
		return $this->operators('or', $property);
	}

	public function and(String $property = null) {
		return $this->operators('and', $property);
	}

	private function operators(String $operator, String $property = null) {
		switch ($this->chain) {
			case 'where':  return $this->whereOperator($operator, $property);  break;
			case 'having': return $this->havingOperator($operator, $property); break;
			default:
				throw new \Exception('Invalid chain "' + $this->chain + '"');
		}
	}

	private function whereOperator(String $operator, String $property = null) {
		if (!count($this->whereConditions)) {
			throw new \Exception('The "criteria()" method should be called at least once');
		}

		$last = count($this->whereConditions) - 1;

		if ($last >= 0) {
			$this->whereConditions[$last][2] = $operator;
		}

		if (!is_null($property)) {
			return $this->where($property);
		} else {
			return $this;
		}
	}

	private function havingOperator(String $operator, String $property = null) {
		if (!count($this->havingConditions)) {
			throw new \Exception('The "criteria()" method should be called at least once');
		}

		$last = count($this->havingConditions) - 1;

		if ($last >= 0) {
			$this->havingConditions[$last][2] = $operator;
		}

		if (!is_null($property)) {
			return $this->having($property);
		} else {
			return $this;
		}
	}

}
