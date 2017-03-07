<?php
namespace ORM\Core;

class Shadow {

	private $class;

	private $tableName;

	private $columns;

	private $hasOne;

	private $hasMany;

	private $manyToMany;

	private $belongsTo;

	public function __construct($class) {
		$this->class = $class;
		$this->columns = [];
		$this->hasOne = [];
		$this->hasMany = [];
		$this->manyToMany = [];
		$this->belongsTo = [];
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

	public function addHasOne(Join $join) {
		$join->setShadow($this);
		array_push($this->hasOne, $join);
	}

	public function getHasOne($property = null, $value = null) {
		if ($property && $value) {
			return $this->findByClass('hasOne', $property, $value);
		} else {
			return $this->hasOne;
		}
	}

	public function addHasMany(Join $join) {
		$join->setShadow($this);
		array_push($this->hasMany, $join);
	}

	public function getHasMany($property = null, $value = null) {
		if ($property && $value) {
			return $this->findByClass('hasMany', $property, $value);
		} else {
			return $this->hasMany;
		}
	}

	public function addManyToMany(Join $join) {
		$join->setShadow($this);
		array_push($this->manyToMany, $join);
	}

	public function getManyToMany($property = null, $value = null) {
		if ($property && $value) {
			return $this->findByClass('manyToMany', $property, $value);
		} else {
			return $this->manyToMany;
		}
	}

	public function addBelongsTo(Join $join) {
		$join->setShadow($this);
		array_push($this->belongsTo, $join);
	}

	public function getBelongsTo($property = null, $value = null) {
		if ($property && $value) {
			return $this->findByClass('belongsTo', $property, $value);
		} else {
			return $this->belongsTo;
		}
	}

	public function getId() {
		$id = [];
		
		foreach($this->columns as $column) {
			if ($column->isId()) {
				$id[] = $column;
			}
		}

		if (!count($id)) {
			throw new \Exception("A classe \"{$this->class}\" precisa ter pelo menos uma chave primária", 1);
		}

		return $id[0];
	}

	private function findByClass($where, $property, $value) {
		$method = 'get' . ucfirst($property);

		foreach ($this->$where as $column) {
			if ($column->$method() === $value) {
				return $column;
			}
		}
	}

}