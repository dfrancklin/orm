<?php
namespace ORM\Builders;

use ORM\Orm;
use ORM\Core\Shadow;
use ORM\Core\Join;

class Query {

	use Where;

	private $orm;

	private $connection;

	private $query;

	private $distinct;

	private $target;

	private $joins;

	private $relations;

	private $usedTables;

	public function __construct(Orm $orm, \PDO $connection) {
		if (!$connection) {
			throw new \Exception('Conexão não definida', 1);
		}

		$this->orm = $orm;
		$this->connection = $connection;
		$this->joins = [];
		$this->relations = [];
		$this->usedTables = [];
		$this->conditions = [];
		$this->values = [];
	}

	public function distinct(bool $distinct) {
		$this->distinct = $distinct;

		return $this;
	}

	public function from(String $from, String $alias) {
		$shadow = $this->orm->getShadow($from);
		$shadow->setAlias($alias);
		$this->target = $shadow;

		return $this;
	}

	public function join(String $join, String $alias) {
		$shadow = $this->orm->getShadow($join);
		$shadow->setAlias($alias);
		$this->joins[$join] = $shadow;

		return $this;
	}

	public function joins(Array $joins) {
		$this->joins = [];

		foreach ($joins as $join) {
			if (!is_array($join)) {
				throw new \InvalidArgumentException('');
			}

			$this->join($join[0], $join[1]);
		}

		return $this;
	}

	public function all() {
		$this->generateQuery();

		pr($this->query);
		vd($this->values);

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

		$this->query .= $this->target->getAlias() . '.* FROM ' . $this->target->getTableName();
		$this->query .= ' ' . $this->target->getAlias();

		$this->usedTables[$this->target->getClass()] = $this->target;

		if (count($this->joins)) {
			$this->preProcessJoins($this->target, $this->joins);
			$this->query .= $this->generateJoins(null, $this->relations);
		}

		$this->query .= $this->resolveWhere();
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
				$inverseShadow = $this->orm->getShadow($reference);
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
				array_key_exists($shadow->getClass(), $this->usedTables) &&
				$join->getType() !== 'manyToMany') {
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
			$sql .= $join->getShadow()->getTableName() . ' ' . $join->getShadow()->getAlias();
		} else {
			$sql .= $shadow->getTableName() . ' ' . $shadow->getAlias();
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
			$sql .= $shadow->getTableName() . ' ' . $shadow->getAlias();
		} else {
			$sql .= $join->getShadow()->getTableName();
		}

		$sql .= "\n\t\t" . ' ON ';
		$sql .= $join->getShadow()->getAlias() . '.';
		$sql .= $join->getShadow()->getId()->getName() . ' = ';
		$sql .= $shadow->getAlias() . '.';

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
			$sql .= ' ' . $join->getShadow()->getAlias();
		} else {
			$sql .= $join->getJoinTable()->getTableName();
		}

		$sql .= "\n\t\t" . ' ON ';
		$sql .= $join->getShadow()->getAlias() . '.';
		$sql .= $join->getShadow()->getId()->getName() . ' = ';
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
