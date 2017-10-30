<?php

namespace ORM\Builders;

use ORM\Orm;
use ORM\Core\Column;
use ORM\Core\Driver;
use ORM\Core\Join;
use ORM\Core\Proxy;

use ORM\Interfaces\IEntityManager;

class Merge {

	private $em;

	private $orm;

	private $shadow;

	private $object;

	private $original;

	private $connection;

	public function __construct(\PDO $connection, IEntityManager $em) {
		if (!$connection) {
			throw new \Exception('Conexão não definida');
		}

		$this->orm = Orm::getInstance();
		$this->em = $em;
		$this->connection = $connection;
	}

	public function exec($object, $original=null) {
		if (!is_object($object)) {
			return;
		}

		$proxy = null;

		if ($object instanceof Proxy) {
			$proxy = $object;
			$object = $object->__getObject();
		}

		if (!is_null($original) && $object === $original) {
			if ($proxy) {
				$proxy->__setObject($object);
				$object = $proxy;
			}

			return $object;
		}

		$class = get_class($object);
		$this->object = $object;
		$this->original = $object;
		$this->shadow = $this->orm->getShadow($class);
		$id = $this->shadow->getId()->getProperty();

		$this->updateBefore();

		if (!($query = $this->generateQuery())) {
			throw new \Exception('The object of the class "' . $this->shadow->getClass() . '" seems to be empty');
		}

		$statement = $this->connection->prepare($query);
		$executed = $statement->execute($this->values);

		$this->updateManyToMany();
		$this->updateAfter();

		if ($proxy) {
			$proxy->__setObject($object);
			$object = $proxy;
		}

		return $this->object;
	}

	private function updateManyToMany() {
		foreach ($this->shadow->getJoins() as $join) {
			if ($join->getType() === 'manyToMany') {
				$property = $join->getProperty();

				if (empty($join->getMappedBy())
						&& in_array('UPDATE', $join->getCascade())
						&& !empty($this->object->{$property})) {
					$this->updateManyCascade($join);
					$this->deleteManyToMany($join);
					$this->insertManyToMany($join);
				}
			}
		}
	}

	private function deleteManyToMany($join) {
		$reference = $this->orm->getShadow($join->getReference());
		$property = $join->getProperty();
		$joinTable = null;
		$column = null;
		$id = null;

		if ($join->getMappedBy()) {
			$referenceJoin = null;
			$referenceJoins = $reference->getJoins('reference', $this->shadow->getClass());

			foreach ($referenceJoins as $j) {
				if ($j->getType() === 'manyToMany') {
					$referenceJoin = $j;
				}
			}

			if (empty($referenceJoin)) {
				return;
			}

			$joinTable = $referenceJoin->getJoinTable();
			$column = $joinTable->getInverseJoinColumnName();
			$id = $reference->getId()->getProperty();
		} else {
			$joinTable = $join->getJoinTable();
			$column = $joinTable->getJoinColumnName();
			$id = $this->shadow->getId()->getProperty();
		}

		$template = 'DELETE FROM %s WHERE %s = %s';
		$table = $joinTable->getTableName();
		$bind = ':' . $column;
		$values[$bind] = $this->object->{$id};

		$sql = sprintf($template, $table, $column, $bind);

		$statement = $this->connection->prepare($sql);
		$statement->execute($values);
	}

	private function insertManyToMany($join) {
		$reference = $this->orm->getShadow($join->getReference());
		$property = $join->getProperty();
		$joinTable = null;

		if ($join->getMappedBy()) {
			$referenceJoin = null;
			$referenceJoins = $reference->getJoins('reference', $this->shadow->getClass());

			foreach ($referenceJoins as $j) {
				if ($j->getType() === 'manyToMany') {
					$referenceJoin = $j;
				}
			}

			if (empty($referenceJoin)) {
				return;
			}

			$joinTable = $referenceJoin->getJoinTable();
		} else {
			$joinTable = $join->getJoinTable();
		}

		$template = 'INSERT INTO %s (%s) VALUES (%s)';
		$table = $joinTable->getTableName();
		$columns = [$joinTable->getJoinColumnName(), $joinTable->getInverseJoinColumnName()];
		$binds = [':' . $joinTable->getJoinColumnName(), ':' . $joinTable->getInverseJoinColumnName()];
		$values = [];
		$sql = sprintf($template, $table, implode(', ', $columns), implode(', ', $binds));

		$id = $this->shadow->getId()->getProperty();
		$referenceId = $reference->getId()->getProperty();

		foreach($this->object->{$property} as $p) {
			$values[':' . $joinTable->getJoinColumnName()] = $this->object->{$id};
			$values[':' . $joinTable->getInverseJoinColumnName()] = $p->{$referenceId};

			$statement = $this->connection->prepare($sql);
			$statement->execute($values);
		}
	}

	private function updateBefore() {
		foreach ($this->shadow->getJoins() as $join) {
			if ($join->getType() === 'belongsTo' &&
					in_array('UPDATE', $join->getCascade())) {
				$this->updateCascade($join);
			}
		}
	}

	private function updateAfter() {
		foreach ($this->shadow->getJoins() as $join) {
			if (in_array($join->getType(), ['hasOne', 'hasMany']) &&
					in_array('UPDATE', $join->getCascade())) {
				if ($join->getType() === 'hasOne') {
					$this->updateCascade($join);
				} elseif ($join->getType() === 'hasMany') {
					$this->updateManyCascade($join);
				}
			}
		}
	}

	private function updateCascade($join) {
		$property = $join->getProperty();
		$value = $this->object->{$property};
		$this->object->{$property} = $this->_merge($join, $value);
	}

	private function updateManyCascade($join) {
		$property = $join->getProperty();
		$values = $this->object->{$property};

		if (!is_array($values)) {
			return;
		}

		foreach($values as $key => $value) {
			$this->object->{$property}[$key] = $this->_merge($join, $value);
		}
	}

	private function _merge($join, $value) {
		if (!is_object($value)) {
			return $value;
		}

		$property = $join->getProperty();
		$reference = $join->getReference();

		$proxy = null;

		if ($value instanceof Proxy) {
			$proxy = $value;
			$value = $value->__getObject();
		}

		$class = get_class($value);

		if ($class !== $reference) {
			throw new \Exception('The type of the property "' . $this->shadow->getClass() .'::' . $property . '"
									should be "' . $reference . '", but "' . $class . '" was given');
		}

		$shadow = $this->orm->getShadow($class);
		$id = $shadow->getId()->getProperty();
		$builder = Merge::class;

		if (!$this->em->find($class, $value->{$id})) {
			if (in_array('INSERT', $join->getCascade())) {
				$builder = Persist::class;
			} else {
				if ($proxy) {
					$proxy->__setObject($value);
					$value = $proxy;
				}

				return $value;
			}
		}

		$builder = new $builder($this->connection, $this->em);
		$newValue = $builder->exec($value, $this->object);

		if ($newValue) {
			if ($proxy) {
				$proxy->__setObject($newValue);
				$newValue = $proxy;
			}

			return $newValue;
		}
	}

	private function generateQuery() {
		$sql = 'UPDATE %s SET %s WHERE %s = %s';

		$idName = $idBind = null;
		$sets = [];
		$values = [];

		foreach (array_merge($this->shadow->getColumns(), $this->shadow->getJoins()) as $column) {
			if ($column instanceof Join && $column->getType() !== 'belongsTo') {
				continue;
			}

			if ($column instanceof Column && $column->isId()) {
				$idName = $column->getProperty();
				$idBind = ':' . $column->getProperty();
				$values[$idBind] = $this->object->{$column->getProperty()};

				continue;
			}

			if ($column instanceof Join) {
				$join = $this->object->{$column->getProperty()};

				if (!is_null($join)) {
					if (is_object($join)) {
						$class = $column->getReference();
						$reference = $this->orm->getShadow($class);
						$id = $reference->getId()->getProperty();

						if (!empty($join->{$id})) {
							$name = $column->getName();
							$bind = ':' . $name;
							$sets[] = sprintf('%s = %s', $name, $bind);
							$values[$bind] = $join->{$id};
						}
					} else {
						$name = $column->getName();
						$bind = ':' . $name;
						$sets[] = sprintf('%s = %s', $name, $bind);
						$values[$bind] = null;
					}
				}
			} else {
				$name = $column->getName();
				$bind = ':' . $name;
				$value = $this->object->{$column->getProperty()};

				$sets[] = sprintf('%s = %s', $name, $bind);
				$values[$bind] = $this->convertValue($value, $column->getType());
			}
		}

		$query = sprintf($sql, $this->shadow->getTableName(), implode(', ', $sets), $idName, $idBind);
		$this->values = $values;

		return !empty($sets) ? $query : false;
	}

	private function convertValue($value, $type) {
		if ($value instanceof \DateTime) {
			$format = Driver::$FORMATS[$type] ?? 'Y-m-d';
			return $value->format($format);
		} else {
			return $value;
		}
	}

}
