<?php

namespace ORM\Mappers;

class Shadow
{

	private $class;

	private $tableName;

	private $schema;

	private $mutable;

	private $alias;

	private $columns;

	private $joins;

	public function __construct($class)
	{
		$this->class = $class;
		$this->mutable = true;
		$this->columns = [];
		$this->joins = [];
	}

	public function getClass() : String
	{
		return $this->class;
	}

	public function getTableName() : String
	{
		return $this->tableName;
	}

	public function setTableName(String $tableName)
	{
		$this->tableName = $tableName;
	}

	public function getSchema()
	{
		return $this->schema;
	}

	public function setSchema(String $schema) : String
	{
		$this->schema = $schema;
	}

	public function isMutable() : bool
	{
		return $this->mutable;
	}

	public function setMutable(bool $mutable)
	{
		$this->mutable = $mutable;
	}

	public function getAlias() : String
	{
		return $this->alias;
	}

	public function setAlias(String $alias)
	{
		$this->alias = $alias;
	}

	public function addColumn(Column $column)
	{
		$column->setShadow($this);
		array_push($this->columns, $column);
	}

	public function getColumns() : Array
	{
		return $this->columns;
	}

	public function addJoin(Join $join)
	{
		$join->setShadow($this);
		array_push($this->joins, $join);
	}

	public function getJoins(String $property = null, $value = null) : Array
	{
		if ($property && $value) {
			return $this->findByProperty('joins', $property, $value);
		} else {
			return $this->joins;
		}
	}

	public function getId() : Column
	{
		$id = null;

		foreach ($this->columns as $column) {
			if ($column->isId()) {
				$id = $column;
				break;
			}
		}

		if (empty($id)) {
			throw new \Exception("A classe \"{$this->class}\" precisa ter pelo menos uma chave primária", 1);
		}

		return $id;
	}

	public function findColumn(String $property) : Column
	{
		$column = null;
		$result = $this->findByProperty('columns', 'property', $property);

		if (!empty($result)) {
			$column = $result[0];
		} else {
			$result = $this->findByProperty('joins', 'property', $property);

			if (!empty($result)) {
				$column = $result[0];
			}
		}

		return $column;
	}

	private function findByProperty(String $list, String $property, $value) : Array
	{
		$method = 'get' . ucfirst($property);
		$columns = [];

		foreach ($this->$list as $column) {
			if ($column->$method() === $value) {
				$columns[] = $column;
			}
		}

		return $columns;
	}

}
