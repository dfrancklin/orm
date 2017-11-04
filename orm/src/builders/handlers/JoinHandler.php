<?php

namespace ORM\Builders\Handlers;

use ORM\Builders\Query;

use ORM\Constants\JoinTypes;

use ORM\Mappers\Shadow;
use ORM\Mappers\Join;

trait JoinHandler
{

	private $joins;

	private $joinsByAlias;

	private $relations;

	private $usedTables;

	public function join(String $join, String $alias, String $type = null) : Query
	{
		if (array_key_exists($alias, $this->joinsByAlias)) {
			throw new \Exception('A class with the alias "' . $alias . '" already exist');
		}

		if (empty($type)) {
			$type = JoinTypes::INNER;
		}

		if (!in_array($type, JoinTypes::TYPES)) {
			throw new \Exception('The join type informed "' . $type . '" does not exists or is not suppoerted');
		}

		$shadow = $this->orm->getShadow($join);
		$shadow->setAlias($alias);

		$this->joins[$join] = [$shadow, $type];
		$this->joinsByAlias[$alias] = [$shadow, $type];

		return $this;
	}

	public function joins(Array $joins) : Query
	{
		$this->joins = [];

		foreach ($joins as $join) {
			if (!is_array($join)) {
				throw new \InvalidArgumentException('The class name, the alias and the type (optional) must be informed. Ex: [className, alias[, type]]');
			}

			$this->join(...$join);
		}

		return $this;
	}

	private function preProcessJoins($joinInfo, $shadows)
	{
		if (is_null($joinInfo)) {
			return;
		}

		$next = array_shift($shadows);
		list($shadow) = $joinInfo;

		foreach ($shadow->getJoins() as $join) {
			$name = $shadow->getTableName() . '.' . $join->getProperty();

			if (!array_key_exists($name, $this->relations)) {
				$reference = $join->getReference();
				$inverseShadow = $this->orm->getShadow($reference);
				$inverseJoins = $inverseShadow->getJoins('reference', $shadow->getClass());

				if (count($inverseJoins)) {
					foreach ($inverseJoins as $inverseJoin) {
						$isValid = $this->validTypes($join, $inverseJoin);

						if ($isValid) {
							$inverseName = $inverseShadow->getTableName() . '.' . $inverseJoin->getProperty();

							if (!array_key_exists($inverseName, $this->relations)) {
								if (array_key_exists($join->getReference(), $this->joins)) {
									$sh = $this->joins[$join->getReference()];
									$this->relations[$name] = [$sh, $join];
								}
							}
						}
					}
				}
			}
		}

		return $this->preProcessJoins($next, $shadows);
	}

	private function validTypes(Join $join, Join $inverseJoin) : bool
	{
		$valid = false;

		if (($join->getType() === 'hasMany' || $join->getType() == 'hasOne') && $inverseJoin->getType() === 'belongsTo') {
			$valid = true;
		} elseif ($join->getType() === 'belongsTo' && ($inverseJoin->getType() === 'hasMany' || $inverseJoin->getType() == 'hasOne')) {
			$valid = true;
		} elseif ($join->getType() === 'manyToMany' && $inverseJoin->getType() === 'manyToMany') {
			$valid = true;
		}

		return $valid;
	}

	private function resolveJoin() : String
	{
		if (empty($this->joins)) {
			return '';
		}

		$this->preProcessJoins([$this->target], $this->joins);

		$sql = '';

		foreach ($this->relations as $relation) {
			$sql .= $this->generateJoins(...$relation);
		}

		return $sql;
	}

	private function generateJoins(Array $joinInfo, Join $join) : String
	{
		list($shadow, $joinType) = $joinInfo;

		if (array_key_exists($join->getShadow()->getClass(), $this->usedTables) &&
				array_key_exists($shadow->getClass(), $this->usedTables) &&
				$join->getType() !== 'manyToMany') {
			return '';
		}

		$method = 'resolveJoin' . ucfirst($join->getType());
		$sql = $this->$method($shadow, $join, $joinType);
		$this->usedTables[$shadow->getClass()] = $shadow;

		return $sql;
	}

	private function resolveJoinHasOne(Shadow $shadow, Join $join, String $joinType) : String
	{
		$sql = "\n\t" . $joinType . ' JOIN ';

		if (array_key_exists($shadow->getClass(), $this->usedTables)) {
			$tableName = '';

			if (!empty($join->getShadow()->getSchema())) {
				$tableName .= $join->getShadow()->getSchema() . '.';
			} elseif (!empty($this->connection->getDefaultSchema())) {
				$tableName .= $this->connection->getDefaultSchema() . '.';
			}

			$tableName .= $join->getShadow()->getTableName();

			$sql .= $tableName . ' ' . $join->getShadow()->getAlias();
		} else {
			$tableName = '';

			if (!empty($shadow->getSchema())) {
				$tableName .= $shadow->getSchema() . '.';
			} elseif (!empty($this->connection->getDefaultSchema())) {
				$tableName .= $this->connection->getDefaultSchema() . '.';
			}

			$tableName .= $shadow->getTableName();

			$sql .= $tableName . ' ' . $shadow->getAlias();
		}

		$sql .= "\n\t\t" . 'ON ';
		$sql .= $join->getShadow()->getAlias() . '.';

		$_joins = $shadow->getJoins('reference', $join->getShadow()->getClass());
		$belongsTo = null;

		foreach ($_joins as $_join) {
			if ($_join->getType() === 'belongsTo') {
				$belongsTo = $_join;
				break;
			}
		}

		if (!empty($belongsTo)) {
			$sql .= $belongsTo->getName() . ' = ';
		} else {
			$sql .= $shadow->getAlias() . '_';
			$sql .= $shadow->getId()->getName() . ' = ';
		}

		$sql .= $shadow->getAlias() . '.';
		$sql .= $shadow->getId()->getName();

		return $sql;
	}

	private function resolveJoinHasMany(Shadow $shadow, Join $join, String $joinType) : String
	{
		$sql = "\n\t" . $joinType . ' JOIN ';

		if (!array_key_exists($shadow->getClass(), $this->usedTables)) {
			$tableName = '';

			if (!empty($shadow->getSchema())) {
				$tableName .= $shadow->getSchema() . '.';
			} elseif (!empty($this->connection->getDefaultSchema())) {
				$tableName .= $this->connection->getDefaultSchema() . '.';
			}

			$tableName .= $shadow->getTableName();

			$sql .= $tableName . ' ' . $shadow->getAlias();
		} else {
			$tableName = '';

			if (!empty($join->getShadow()->getSchema())) {
				$tableName .= $join->getShadow()->getSchema() . '.';
			} elseif (!empty($this->connection->getDefaultSchema())) {
				$tableName .= $this->connection->getDefaultSchema() . '.';
			}

			$tableName .= $join->getShadow()->getTableName();

			$sql .= $tableName . ' ' . $join->getShadow()->getAlias();
		}

		$sql .= "\n\t\t" . 'ON ';
		$sql .= $join->getShadow()->getAlias() . '.';
		$sql .= $join->getShadow()->getId()->getName() . ' = ';
		$sql .= $shadow->getAlias() . '.';

		$_joins = $shadow->getJoins('reference', $join->getShadow()->getClass());
		$belongsTo = null;

		foreach ($_joins as $_join) {
			if ($_join->getType() === 'belongsTo') {
				$belongsTo = $_join;
				break;
			}
		}

		if (!empty($belongsTo)) {
			$sql .= $belongsTo->getName();
		} else {
			$sql .= $join->getShadow()->getTableName() . '_';
			$sql .= $join->getShadow()->getId()->getName();
		}

		return $sql;
	}

	private function resolveJoinManyToMany(Shadow $shadow, Join $join, String $joinType) : String
	{
		if ($join->getMappedBy()) {
			$tempJoin = $shadow->getJoins('property', $join->getMappedBy());
			$shadow = $join->getShadow();
			$join = $tempJoin[0];
		}

		$sql = "\n\t" . $joinType . ' JOIN ';

		$joinTableName = '';

		if (!empty($join->getJoinTable()->getSchema())) {
			$joinTableName .= $join->getJoinTable()->getSchema() . '.';
		} elseif (!empty($this->connection->getDefaultSchema())) {
			$joinTableName .= $this->connection->getDefaultSchema() . '.';
		}

		$joinTableName .= $join->getJoinTable()->getTableName();
		$joinTableAlias = $shadow->getAlias() . '_' . $join->getShadow()->getAlias();

		if (!array_key_exists($join->getShadow()->getClass(), $this->usedTables)) {
			$tableName = '';

			if (!empty($join->getShadow()->getSchema())) {
				$tableName .= $join->getShadow()->getSchema() . '.';
			} elseif (!empty($this->connection->getDefaultSchema())) {
				$tableName .= $this->connection->getDefaultSchema() . '.';
			}

			$tableName .= $join->getShadow()->getTableName();

			$sql .= $joinTableName . ' ' . $joinTableAlias;
			$sql .= "\n\t\t" . 'ON ';
			$sql .= $joinTableAlias . '.' . $join->getJoinTable()->getInverseJoinColumnName() . ' = ';
			$sql .= $shadow->getAlias() . '.';
			$sql .= $shadow->getId()->getName();

			$sql .= "\n\t" . $joinType . ' JOIN ';
			$sql .= $tableName . ' ' . $join->getShadow()->getAlias();
		} else {
			$sql .= $joinTableName . ' ' . $joinTableAlias;
		}

		$sql .= "\n\t\t" . 'ON ';
		$sql .= $join->getShadow()->getAlias() . '.';
		$sql .= $join->getShadow()->getId()->getName() . ' = ';
		$sql .= $joinTableAlias . '.' . $join->getJoinTable()->getJoinColumnName();

		if (!array_key_exists($shadow->getClass(), $this->usedTables)) {
			$tableName = '';

			if (!empty($shadow->getSchema())) {
				$tableName .= $shadow->getSchema() . '.';
			} elseif (!empty($this->connection->getDefaultSchema())) {
				$tableName .= $this->connection->getDefaultSchema() . '.';
			}

			$tableName .= $shadow->getTableName();

			$sql .= "\n\t" . $joinType . ' JOIN ' . $tableName . ' ' . $shadow->getAlias() . "\n\t\t" . 'ON ';
			$sql .= $shadow->getAlias() . '.' . $shadow->getId()->getName() . ' = ';
			$sql .= $joinTableAlias . '.' . $join->getJoinTable()->getInverseJoinColumnName();
		}

		return $sql;
	}

	private function resolveJoinBelongsTo(Shadow $shadow, Join $join, String $joinType) : String
	{
		$sql = "\n\t" . $joinType . ' JOIN ';

		if (!array_key_exists($shadow->getClass(), $this->usedTables)) {
			$tableName = '';

			if (!empty($shadow->getSchema())) {
				$tableName .= $shadow->getSchema() . '.';
			} elseif (!empty($this->connection->getDefaultSchema())) {
				$tableName .= $this->connection->getDefaultSchema() . '.';
			}

			$tableName .= $shadow->getTableName();

			$sql .= $tableName;
			$sql .= ' ' . $shadow->getAlias();
		} else {
			$tableName = '';

			if (!empty($join->getShadow()->getSchema())) {
				$tableName .= $join->getShadow()->getSchema() . '.';
			} elseif (!empty($this->connection->getDefaultSchema())) {
				$tableName .= $this->connection->getDefaultSchema() . '.';
			}

			$tableName .= $join->getShadow()->getTableName();

			$sql .= $tableName;
			$sql .= ' ' . $join->getShadow()->getAlias();
		}

		$sql .= "\n\t\t" . 'ON ';
		$sql .= $shadow->getAlias() . '.';
		$sql .= $shadow->getId()->getName() . ' = ';
		$sql .= $join->getShadow()->getAlias() . '.';
		$sql .= $join->getName();

		return $sql;
	}

}
