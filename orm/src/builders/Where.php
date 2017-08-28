<?php

namespace ORM\Builders;

use ORM\Core\Shadow;
use ORM\Core\Column;
use ORM\Core\Driver;

trait Where {

	use Operator;

	private $whereConditions;

	private $values;

	public function where(String $property): Criteria {
		$this->chain = 'where';

		$criteria = new Criteria($this);

		array_push($this->whereConditions, [$property, $criteria]);

		$this->and();

		return $criteria;
	}

	private function resolveWhere() {
		if ($this->whereConditions === null) {
			return;
		}

		$sql = '';

		if (count($this->whereConditions)) {
			$sql .= "\n\t" . ' WHERE ';
		}

		foreach($this->whereConditions as $key => $condition) {
			if (($condition[2] === 'or' && $key === 0) ||
					($condition[2] === 'or' && $key > 0 && $this->whereConditions[$key - 1][2] !== 'or')) {
				$sql .= '(';
			}

			$sql .= $this->resolveCondition(...$condition);

			if ($condition[2] !== 'or' && $key > 0 && $this->whereConditions[$key - 1][2] === 'or') {
				$sql .= ')';
			}

			if ($key < count($this->whereConditions) - 1) {
				$sql .= ' ' . $condition[2] . ' ';

				if ($condition[2] !== 'or') {
					$sql .= "\n\t\t";
				}
			}
		}

		return $sql;
	}

	private function resolveCondition($property, $criteria) {
		$sql = '';

		list($prop, $shadow, $column) = $this->processProperty($property);
		$values = $this->processValues($criteria->getValues(), $column);
		$alias = str_replace('.', '_', $property);
		$args = [$prop];

		if (array_key_exists($alias, $this->values) ||
				array_key_exists($alias . '_1', $this->values)) {
			$count = 2;

			while(array_key_exists($alias . $count, $this->values) ||
					array_key_exists($alias . $count . '_1', $this->values)) {
				$count++;
			}

			$alias = $alias . $count;
		}

		if ($criteria->getAction() === Criteria::BETWEEN) {
			$this->values[$alias . '_1'] = $values[0];
			$this->values[$alias . '_2'] = $values[1];
			array_push($args, ':' . $alias . '_1', ':' . $alias . '_2');
		} elseif (count($values)) {
			$this->values[$alias] = $values[0];
			array_push($args, ':' . $alias);
		}

		$template = $criteria->getTemplate();

		$sql .= vsprintf($template, $args);

		return $sql;
	}

	public function processProperty(String $property) : Array {
		$alias = null;

		if ($index = strpos($property, '.')) {
			$alias = substr($property, 0, $index);
			$property = substr($property, $index + 1);
		}

		if (!($shadow = $this->findShadow($alias))) {
			throw new \Exception('Invalid alias "' . $alias . '"');
		}

		if (!($column = $shadow->findColumn($property))) {
			throw new \Exception('Invalid property "' . $property . '"');
		}

		$property = $alias . '.' . $column->getName();

		return [$property, $shadow, $column];
	}

	public function processValues(Array $values, Column $column) : Array {
		$processedValues = [];

		foreach ($values as $value) {
			if ($value instanceof \DateTime) {
				$format = Driver::$FORMATS[$column->getType()] ?? 'Y-m-d';
				array_push($processedValues, $value->format($format));
			} else {
				array_push($processedValues, $value);
			}
		}

		return $processedValues;
	}

	private function findShadow(String $alias) {
		$shadow = null;

		if (!array_key_exists($alias, $this->joinsByAlias)) {
			throw new \Exception('Invalid alias "' . $alias . '"');
		}

		return $this->joinsByAlias[$alias];
	}

}
