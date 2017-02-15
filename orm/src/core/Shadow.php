<?php
namespace ORM\Core;

class Shadow {

	private $class;

	private $tableName;

	private $columns;

	private $hasMany;

	private $hasOne;

	private $belongsTo;

	public function __construct($class) {
		$this->class = $class;
		$this->columns = [];
		$this->hasMany = [];
		$this->hasOne = [];
		$this->belongsTo = [];
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

	public function addHasMany(Join $join) {
		$join->setShadow($this);
		array_push($this->hasMany, $join);
	}

	public function addHasOne(Join $join) {
		$join->setShadow($this);
		array_push($this->hasOne, $join);
	}

	public function addBelongsTo(Join $join) {
		$join->setShadow($this);
		array_push($this->belongsTo, $join);
	}

}