<?php
namespace ORM\Core;

class Shadow {

	private $class;

	private $tableName;

	private $columns;

	private $joins;

	public function __construct($class) {
		$this->class = $class;
		$this->columns = [];
		$this->joins = [];
	}

	public function getClass() {
		return $this->class;
	}

	public function getTableName() {
		return $this->tableName;
	}

	public function setTableName($tableName) {
		$this->tableName = $tableName;
	}

	public function addColumn(Column $column) {
		$column->setShadow($this);
		array_push($this->columns, $column);
	}

	public function getColumns() {
		return $this->columns;
	}

	public function addJoin(Join $join) {
		$join->setShadow($this);
		array_push($this->joins, $join);
	}

	public function getJoins($property = null, $value = null) {
		if ($property && $value) {
			return $this->findByProperty('joins', $property, $value);
		} else {
			return $this->joins;
		}
	}

	public function getId() {
		$ids = [];
		
		foreach($this->columns as $column) {
			if ($column->isId()) {
				array_push($ids, $column);
			}
		}

		if (!count($ids)) {
			throw new \Exception("A classe \"{$this->class}\" precisa ter pelo menos uma chave primária", 1);
		}

		return $ids[0];
	}

	private function findByProperty($list, $property, $value) {
		$method = 'get' . ucfirst($property);
		$columns = [];

		foreach ($this->$list as $column) {
			if ($column->$method() === $value) {
				array_push($columns, $column);
			}
		}

		return $columns;
	}

}