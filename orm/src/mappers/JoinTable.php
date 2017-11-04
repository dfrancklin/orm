<?php

namespace ORM\Core;

class JoinTable
{

	private $tableName;

	private $schema;

	private $joinColumnName;

	private $inverseJoinColumnName;

	public function getTableName() : String
	{
		return $this->tableName;
	}

	public function setTableName(String $tableName)
	{
		$this->tableName = $tableName;
	}

	public function getSchema() : String
	{
		return $this->schema;
	}

	public function setSchema(String $schema)
	{
		$this->schema = $schema;
	}

	public function getJoinColumnName() : String
	{
		return $this->joinColumnName;
	}

	public function setJoinColumnName(String $joinColumnName)
	{
		$this->joinColumnName = $joinColumnName;
	}

	public function getInverseJoinColumnName() : String
	{
		return $this->inverseJoinColumnName;
	}

	public function setInverseJoinColumnName(String $inverseJoinColumnName)
	{
		$this->inverseJoinColumnName = $inverseJoinColumnName;
	}

}
