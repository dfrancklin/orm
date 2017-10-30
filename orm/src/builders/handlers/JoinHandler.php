<?php

namespace ORM\Builders\Handlers;

use ORM\Core\Shadow;
use ORM\Core\Join;

trait JoinHandler {

	private static
			$INNER = 'INNER',
			$LEFT = 'LEFT',
			$RIGHT = 'RIGHT',
			$JOIN_TYPES = [];

	private static $initialized;

	private $joins;

	private $joinsByAlias;

	private $relations;

	private $usedTables;

	private static function initializeJoinHandler() {
		self::$JOIN_TYPES = [
			self::$INNER,
			self::$LEFT,
			self::$RIGHT
		];
	}

	public function join(String $join, String $alias, String $type=null) {
		if (!self::$initialized) {
			self::initializeJoinHandler();
		}

		if (array_key_exists($alias, $this->joinsByAlias)) {
			throw new \Exception('A class with the alias "' . $alias . '" already exist');
		}

		if (empty($type)) {
			$type = self::$INNER;
		}

		if (!in_array($type, self::$JOIN_TYPES)) {
			throw new \Exception('The join type informed "' . $type . '" does not exists or is not suppoerted');
		}

		$shadow = $this->orm->getShadow($join);
		$shadow->setAlias($alias);

		$this->joins[$join] = [$shadow, $type];
		$this->joinsByAlias[$alias] = [$shadow, $type];

		return $this;
	}

	public function joins(Array $joins) {
		$this->joins = [];

		foreach ($joins as $join) {
			if (!is_array($join)) {
				throw new \InvalidArgumentException('The class name, the alias and the type (optional) must be informed. Ex: [className, alias[, type]]');
			}

			$this->join(...$join);
		}

		return $this;
	}

	private function preProcessJoins($joinInfo, $shadows) {
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

	private function resolveJoin() {
		if (empty($this->joins)) {
			return;
		}

		$this->preProcessJoins([$this->target], $this->joins);

		$sql = '';

		foreach ($this->relations as $relation) {
			$sql .= $this->generateJoins(...$relation);
		}

		return $sql;
	}

	private function generateJoins(Array $joinInfo, Join $join) {
		list($shadow, $joinType) = $joinInfo;

		if (array_key_exists($join->getShadow()->getClass(), $this->usedTables) &&
				array_key_exists($shadow->getClass(), $this->usedTables) &&
				$join->getType() !== 'manyToMany') {
			return;
		}

		$method = 'resolveJoin' . ucfirst($join->getType());
		$sql = $this->$method($shadow, $join, $joinType);
		$this->usedTables[$shadow->getClass()] = $shadow;

		return $sql;
	}

	private function resolveJoinHasOne(Shadow $shadow, Join $join, String $joinType) {
		$sql = "\n\t" . $joinType . ' JOIN ';

		if (array_key_exists($shadow->getClass(), $this->usedTables)) {
			$sql .= $join->getShadow()->getTableName() . ' ' . $join->getShadow()->getAlias();
		} else {
			$sql .= $shadow->getTableName() . ' ' . $shadow->getAlias();
		}

		$sql .= "\n\t\t" . 'ON ';
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

	private function resolveJoinHasMany(Shadow $shadow, Join $join, String $joinType) {
		$sql = "\n\t" . $joinType . ' JOIN ';

		if (!array_key_exists($shadow->getClass(), $this->usedTables)) {
			$sql .= $shadow->getTableName() . ' ' . $shadow->getAlias();
		} else {
			$sql .= $join->getShadow()->getTableName();
		}

		$sql .= "\n\t\t" . 'ON ';
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

	private function resolveJoinManyToMany(Shadow $shadow, Join $join, String $joinType) {
		if ($join->getMappedBy()) {
			$tempJoin = $shadow->getJoins('property', $join->getMappedBy());
			$shadow = $join->getShadow();
			$join = $tempJoin[0];
		}

		$sql = "\n\t" . $joinType . ' JOIN ';

		if (!array_key_exists($join->getShadow()->getClass(), $this->usedTables)) {
			$sql .= $join->getJoinTable()->getTableName();
			$sql .= "\n\t\t" . 'ON ';
			$sql .= $join->getJoinTable()->getTableName() . '.' . $join->getJoinTable()->getInverseJoinColumnName() . ' = ';
			$sql .= $shadow->getAlias() . '.';
			$sql .= $shadow->getId()->getName();

			$sql .= "\n\t" . $joinType . ' JOIN ';
			$sql .= $join->getShadow()->getTableName();
			$sql .= ' ' . $join->getShadow()->getAlias();
		} else {
			$sql .= $join->getJoinTable()->getTableName();
		}

		$sql .= "\n\t\t" . 'ON ';
		$sql .= $join->getShadow()->getAlias() . '.';
		$sql .= $join->getShadow()->getId()->getName() . ' = ';
		$sql .= $join->getJoinTable()->getTableName() . '.' . $join->getJoinTable()->getJoinColumnName();

		if (!array_key_exists($shadow->getClass(), $this->usedTables)) {
			$sql .= "\n\t" . $joinType . ' JOIN ' . $shadow->getTableName() . ' ' . $shadow->getAlias() . "\n\t\t" . 'ON ';
			$sql .= $shadow->getAlias() . '.' . $shadow->getId()->getName() . ' = ';
			$sql .= $join->getJoinTable()->getTableName() . '.' . $join->getJoinTable()->getInverseJoinColumnName();
		}

		return $sql;
	}

	private function resolveJoinBelongsTo(Shadow $shadow, Join $join, String $joinType) {
		$sql = "\n\t" . $joinType . ' JOIN ';

		if (array_key_exists($shadow->getClass(), $this->usedTables)) {
			$sql .= $join->getShadow()->getTableName();
		} else {
			$sql .= $shadow->getTableName();
			$sql .= ' ' . $shadow->getAlias();
		}

		$sql .= "\n\t\t" . 'ON ';
		$sql .= $shadow->getAlias() . '.';
		$sql .= $shadow->getId()->getName() . ' = ';
		$sql .= $join->getShadow()->getAlias() . '.';
		$sql .= $join->getName();

		return $sql;
	}

}
