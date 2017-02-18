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

	public function addHasMany(Join $join) {
		$join->setShadow($this);
		array_push($this->hasMany, $join);
	}

	public function getHasMany() {
		return $this->hasMany;
	}

	public function addHasOne(Join $join) {
		$join->setShadow($this);
		array_push($this->hasOne, $join);
	}

	public function getHasOne() {
		return $this->hasOne;
	}

	public function addBelongsTo(Join $join) {
		$join->setShadow($this);
		array_push($this->belongsTo, $join);
	}

	public function getBelongsTo() {
		return $this->belongsTo;
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

}