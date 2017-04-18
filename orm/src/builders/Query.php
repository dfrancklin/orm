<?php

namespace ORM\Builders;

use ORM\Orm;
use ORM\Core\Shadow;
use ORM\Core\Join;

class Query extends Orm {

	use Where;

	private $connection;
	private $query;
	private $distinct;
	private $from;
	private $joins;
	private $relations;
	private $usedTables;

	public function __construct($connection, $from = '', $joins = []) {
		if (!$connection) {
			throw new \Exception('Conexão não definida', 1);
		}

		$this->connection = $connection;

		if ($from) {
			$this->from($from);
		}

		if (count($joins)) {
			$this->joins($joins);
		}

		$this->relations = [];
		$this->usedTables = [];
	}

	public function distinct(bool $distinct) {
		$this->distinct = $distinct;

		return $this;
	}

	public function from(String $from) {
		$this->from = self::getShadow($from);

		return $this;
	}

	public function joins(Array $joins) {
		$this->joins = [];

		foreach ($joins as $join) {
			$this->joins[$join] = self::getShadow($join);
		}

		return $this;
	}

	public function all() {
		$this->generateQuery();

		pre($this->query);

		$query = $this->connection->query($this->query);

		if ($query) {
			return $query->fetchAll(\PDO::FETCH_ASSOC);
		}
	}

	private function generateQuery() {
		$this->query = 'SELECT ';

		if ($this->distinct) {
			$this->query .= 'DISTINCT ';
		}

		$this->query .= $this->from->getTableName() . '.* FROM ' . $this->from->getTableName();

		$this->usedTables[$this->from->getClass()] = $this->from;

		if (count($this->joins)) {
			$this->preProcessJoins($this->from, $this->joins);
			$this->query .= $this->generateJoins(null, $this->relations);
		}
	}

	private function preProcessJoins($shadow, $shadows) {
		if (is_null($shadow)) {
			return;
		}

		$next = array_shift($shadows);

		foreach ($shadow->getJoins() as $join) {
			$name = $shadow->getTableName() . '.' . $join->getProperty();

			if (!array_key_exists($name, $this->relations)) {
				$reference = $join->getReference();
				$inverseShadow = self::getShadow($reference);
				$inverseJoins = $inverseShadow->getJoins('reference', $shadow->getClass());

				if (count($inverseJoins)) {
					foreach ($inverseJoins as $inverseJoin) {
						if ($this->validTypes($join, $inverseJoin)) {
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

	private function validTypes(Join $join, Join $inverseJoin) {
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

	private function generateJoins($relation, Array $relations) {
		if (!$relation) {
			if (count($relations)) {
				return $this->generateJoins(array_shift($relations), $relations);
			} else {
				return;
			}
		}

		$this->query .= $this->resolveJoin($relation[0], $relation[1]);

		return $this->generateJoins(array_shift($relations), $relations);
	}

	private function resolveJoin(Shadow $shadow, Join $join) {
		if (array_key_exists($join->getShadow()->getClass(), $this->usedTables) &&
				array_key_exists($shadow->getClass(), $this->usedTables)) {
			return;
		}

		$method = 'resolveJoin' . ucfirst($join->getType());
		$sql = $this->$method($shadow, $join);
		$this->usedTables[$shadow->getClass()] = $shadow;

		return $sql;
	}

	private function resolveJoinHasOne(Shadow $shadow, Join $join) {
		$sql = "\n\t" . ' INNER JOIN ';

		if (array_key_exists($shadow->getClass(), $this->usedTables)) {
			$sql .= $join->getShadow()->getTableName();
		} else {
			$sql .= $shadow->getTableName();
		}

		$sql .= "\n\t\t" . ' ON ';
		$sql .= $join->getShadow()->getTableName() . '.';

		$belongsTo = $shadow->getJoins('reference', $join->getShadow()->getClass());

		if (!is_array($belongsTo) && $belongsTo) {
			$sql .= $belongsTo->getName() . ' = ';
		} else {
			$sql .= $shadow->getTableName() . '_';
			$sql .= $shadow->getId()->getName() . ' = ';
		}

		$sql .= $shadow->getTableName() . '.';
		$sql .= $shadow->getId()->getName();

		return $sql;
	}

	private function resolveJoinHasMany(Shadow $shadow, Join $join) {
		$sql = "\n\t" . ' INNER JOIN ';

		if (!array_key_exists($shadow->getClass(), $this->usedTables)) {
			$sql .= $shadow->getTableName();
		} else {
			$sql .= $join->getShadow()->getTableName();
		}

		$sql .= "\n\t\t" . ' ON ';
		$sql .= $join->getShadow()->getTableName() . '.';
		$sql .= $join->getShadow()->getId()->getName() . ' = ';
		$sql .= $shadow->getTableName() . '.';

		$belongsTo = $shadow->getJoins('reference', $join->getShadow()->getClass());

		if (!is_array($belongsTo) && $belongsTo) {
			$sql .= $belongsTo->getName();
		} else {
			$sql .= $join->getShadow()->getTableName() . '_';
			$sql .= $join->getShadow()->getId()->getName();
		}

		return $sql;
	}

	private function resolveJoinManyToMany(Shadow $shadow, Join $join) {
		if ($join->getMappedBy()) {
			$tempJoin = $shadow->getJoins('property', $join->getMappedBy());
			$shadow = $join->getShadow();
			$join = $tempJoin[0];
		}

		$sql = "\n\t" . ' INNER JOIN ';

		if (!array_key_exists($join->getShadow()->getClass(), $this->usedTables)) {
			$sql .= $join->getShadow()->getTableName();
		} else {
			$sql .= $join->getJoinTable()->getTableName();
		}

		$sql .= "\n\t\t" . ' ON ';
		$sql .= $join->getShadow()->getTableName() . '.' . $join->getShadow()->getId()->getName() . ' = ';
		$sql .= $join->getJoinTable()->getTableName() . '.' . $join->getJoinTable()->getJoinColumnName();

		if (!array_key_exists($shadow->getClass(), $this->usedTables)) {
			$sql .= "\n\t" . ' INNER JOIN ' . $shadow->getTableName() . "\n\t\t" . ' ON ';
			$sql .= $shadow->getTableName() . '.' . $shadow->getId()->getName() . ' = ';
			$sql .= $join->getJoinTable()->getTableName() . '.' . $join->getJoinTable()->getInverseJoinColumnName();
		}

		return $sql;
	}

	private function resolveJoinBelongsTo(Shadow $shadow, Join $join) {
		$sql = "\n\t" . ' INNER JOIN ';

		if (array_key_exists($shadow->getClass(), $this->usedTables)) {
			$sql .= $join->getShadow()->getTableName();
		} else {
			$sql .= $shadow->getTableName();
		}

		$sql .= "\n\t\t" . ' ON ';
		$sql .= $shadow->getTableName() . '.';
		$sql .= $shadow->getId()->getName() . ' = ';
		$sql .= $join->getShadow()->getTableName() . '.';
		$sql .= $join->getName();

		return $sql;
	}

}
