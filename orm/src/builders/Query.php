<?php
namespace ORM\Builders;

use ORM\Orm;

class Query extends Orm {

	use Where;

	private $connection;

	public $query;

	private $distinct;

	private $from;

	private $joins;

	private $relations;

	public function __construct($connection, $from = '', $joins = []) {
		if (!$connection)
			throw new \Exception('Conexão não definida', 1);

		$this->connection = $connection;

		if ($from)
			$this->from($from);

		if (count($joins))
			$this->joins($joins);

		$this->relations = [];
	}

	public function distinct(bool $distinct) {
		$this->distinct = $distinct;

		return $this;
	}

	public function from($from) {
		$this->from = self::getShadow($from);

		return $this;
	}

	public function joins($joins) {
		$this->joins = [];

		foreach($joins as $join) {
			if (is_array($join)) {
				$this->joins[$join[0]][0] = self::getShadow($join[0]);
				$this->joins[$join[0]][1] = $join[1];
			} else {
				$this->joins[$join] = self::getShadow($join);
			}
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

		if (count($this->joins)) {
			$this->preProcessJoins($this->from, $this->joins);

			vd(array_keys($this->relations));
			
			die();

			$this->query .= $this->generateJoins($this->from, $this->joins);
		}
	}

	private function preProcessJoins($shadow, $shadows) {
		if (is_null($shadow)) return;

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
							$inverseName = $inverseShadow->getTableName() . '.';
							$inverseName .= $inverseJoin->getProperty();

							if (!array_key_exists($inverseName, $this->relations)) {
								$this->relations[$name] = [$shadow, $join];
							}
						}
					}
				}
			}
		}

		return $this->preProcessJoins($next, $shadows);
	}

	private function validTypes($join, $inverseJoin) {
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

	private function generateJoins($shadow, $shadows) {
		if (!$shadow) return;
		
		$joins = array_merge($shadow->getHasOne(), $shadow->getHasMany(), $shadow->getManyToMany(), $shadow->getBelongsTo());
		
		foreach($joins as $join) {
			$reference = $join->getReference();

			if (array_key_exists($reference, $this->joins)) {
				$this->query .= $this->resolveJoin($join, $this->joins[$reference]);
				$this->usedTables[$reference] = $this->joins[$reference];

				if (array_key_exists($shadow->getClass(), $this->joins)) {
					$this->usedTables[$shadow->getClass()] = $this->joins[$shadow->getClass()];
				}
			} elseif ($this->from->getClass() === $reference) {
				$this->query .= $this->resolveJoin($join,  $this->from);

				$reference = $join->getShadow()->getClass();

				if (array_key_exists($reference, $this->joins)) {
					$this->usedTables[$reference] = $this->joins[$reference];
				}
			}
		}
		
		$next = array_shift($shadows);
		
		return $this->generateJoins($next, $shadows);
	}

	private function resolveJoin($join, $shadow) {
		if (array_key_exists($join->getShadow()->getClass(), $this->usedTables) &&
			array_key_exists($shadow->getClass(), $this->usedTables)) {
			return;
		}
		
		$method = 'resolveJoin' . ucfirst($join->getType());

		return $this->$method($join, $shadow);
	}

	private function resolveJoinHasOne($join, $shadow) {
		$sql = "\n\t" . ' INNER JOIN ';

		if (array_key_exists($shadow->getClass(), $this->usedTables)) {
			$sql .= $join->getShadow()->getTableName();
		} else {
			$sql .= $shadow->getTableName();
		}

		$sql .= ' ______' . $join->getProperty() . '______';
		$sql .= ' ON ';
		$sql .= $join->getShadow()->getTableName() . '.';
		
		$belongsTo = $shadow->getBelongsTo('reference', $join->getShadow()->getClass());
		
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
	
	private function resolveJoinHasMany($join, $shadow) {
		$sql = "\n\t" . ' INNER JOIN ';

		if (array_key_exists($shadow->getClass(), $this->usedTables)) {
			$sql .= $join->getShadow()->getTableName();
		} else {
			$sql .= $shadow->getTableName();
		}
		
		$sql .= ' ______' . $join->getProperty() . '______';
		$sql .= ' ON ';
		$sql .= $join->getShadow()->getTableName() . '.';
		$sql .= $join->getShadow()->getId()->getName() . ' = ';
		$sql .= $join->getProperty() . '.';

		$belongsTo = $shadow->getBelongsTo('reference', $join->getShadow()->getClass());
		
		if (!is_array($belongsTo) && $belongsTo) {
			$sql .= $belongsTo->getName();
		} else {
			$sql .= $join->getShadow()->getTableName() . '_';
			$sql .= $join->getShadow()->getId()->getName();
		}
		
		return $sql;
	}

	public function resolveJoinManyToMany($join, $shadow) {
		if ($join->getMappedBy()) {
			$tempJoin = $shadow->getManyToMany('property', $join->getMappedBy());
			$shadow = $join->getShadow();
			$join = $tempJoin;
		}

		$sql = "\n\t" . ' INNER JOIN ';
		
		if (array_key_exists($shadow->getClass(), $this->usedTables)) {
			$sql .= $join->getJoinTable()->getTableName();
			$sql .= ' ON ';
			$sql .= $shadow->getTableName() . '.';
			$sql .= $shadow->getId()->getName() . ' = ';
			$sql .= $join->getJoinTable()->getTableName() . '.';
			$sql .= $join->getJoinTable()->getInverseJoinColumnName();
		}


		$sql .= "\n\t" . ' INNER JOIN ';
		$sql .= $join->getShadow()->getTableName();
		$sql .= ' ' . '______' . $join->getProperty() . '______';
		$sql .= ' ON ';
		$sql .= $join->getShadow()->getTableName() . '.';
		$sql .= $join->getShadow()->getId()->getName() . ' = ';
		$sql .= $join->getJoinTable()->getTableName() . '.';
		$sql .= $join->getJoinTable()->getJoinColumnName();

		return $sql;
	}

	private function resolveJoinBelongsTo($join, $shadow) {
		$sql = "\n\t" . ' INNER JOIN ';

		if (array_key_exists($shadow->getClass(), $this->usedTables)) {
			$sql .= $join->getShadow()->getTableName();	
		} else {
			$sql .= $shadow->getTableName();
		}

		$sql .= ' ______' . $join->getProperty() . '______';
		$sql .= ' ON ';
		$sql .= $shadow->getTableName() . '.';
		$sql .= $shadow->getId()->getName() . ' = ';
		$sql .= $join->getShadow()->getTableName() . '.';
		$sql .= $join->getName();

		return $sql;
	}

}