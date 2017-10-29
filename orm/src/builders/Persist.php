<?php

namespace ORM\Builders;

use ORM\Orm;
use ORM\Core\Column;
use ORM\Core\Driver;
use ORM\Core\Join;
use ORM\Core\Proxy;

use ORM\Interfaces\IEntityManager;

class Persist {

	private $em;

	private $orm;

	private $shadow;

	private $object;

	private $original;

	private $connection;

	private $query;

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
		$column = $this->shadow->getId();
		$id = $column->getProperty();

		$this->persistBefore();

		if (!($query = $this->generateQuery())) {
			throw new \Exception('The object of the class "' . $this->shadow->getClass() . '" seems to be empty');
		}

		$this->object->{$id} = $this->fetchNextId();

		$statement = $this->connection->prepare($query);
		$executed = $statement->execute($this->values);

		if (!$statement->rowCount()) {
			throw new \Exception('Something went wrong while persistting a transaction');
		}

		$lastId = $this->connection->lastInsertId();

		if ($column->getType() === 'int') {
			$this->object->{$id} = (int) $lastId;
		} else {
			$this->object->{$id} = $lastId;
		}

		$this->updateManyToMany();

		$this->persistAfter();

		if ($proxy) {
			$proxy->__setObject($object);
			$object = $proxy;
		}

		return $this->object;
	}

	private function fetchNextId() {
		if (in_array(Driver::$GENERATE_ID_TYPE, ['QUERY', 'SEQUENCE'])) {
			$statement = $this->connection->prepare(Driver::$GENERATE_ID_QUERY);
			$executed = $statement->execute();

			if ($executed) {
				$next = $statement->fetch(\PDO::FETCH_NUM);

				if (!empty($next)) {
					return $next[0];
				}
			}
		}

		return null;
	}

	private function updateManyToMany() {
		foreach ($this->shadow->getJoins() as $join) {
			if ($join->getType() === 'manyToMany') {
				$property = $join->getProperty();

				if (empty($join->getMappedBy()) &&
						in_array('INSERT', $join->getCascade()) &&
						!empty($this->object->{$property})) {
					$this->persistManyCascade($join);
					$this->insertManyToMany($join);
				}
			}
		}
	}

	public function insertManyToMany($join) {
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

	private function persistBefore() {
		foreach ($this->shadow->getJoins() as $join) {
			if ($join->getType() === 'belongsTo' &&
					in_array('INSERT', $join->getCascade())) {
				$this->persistCascade($join);
			}
		}
	}

	private function persistAfter() {
		foreach ($this->shadow->getJoins() as $join) {
			if (in_array($join->getType(), ['hasOne', 'hasMany']) &&
					in_array('INSERT', $join->getCascade())) {
				if ($join->getType() === 'hasOne') {
					$this->persistCascade($join);
				} elseif ($join->getType() === 'hasMany') {
					$this->persistManyCascade($join);
				}
			}
		}
	}

	private function persistCascade($join) {
		$property = $join->getProperty();
		$reference = $join->getReference();
		$value = $this->object->{$property};

		if (!is_object($value)) {
			return;
		}

		if ($value instanceof Proxy) {
			$value = $value->__getObject();
		}

		$class = get_class($value);

		if ($class !== $reference) {
			throw new \Exception('The type of the property "' . $this->shadow->getClass() .'::' . $property . '"
									should be "' . $reference . '", but "' . $class . '" was given');
		}

		$shadow = $this->orm->getShadow($class);
		$id = $shadow->getId()->getProperty();

		if ($this->em->find($class, $value->{$id})) {
			return;
		}

		$persist = new Persist($this->connection, $this->em);
		$newValue = $persist->exec($value, $this->object);

		if ($newValue) {
			$this->object->{$property} = $newValue;
		}
	}

	private function persistManyCascade($join) {
		$property = $join->getProperty();
		$reference = $join->getReference();
		$values = $this->object->{$property};

		if (!is_array($values)) {
			return;
		}

		foreach($values as $key => $value) {
			if (!is_object($value)) {
				continue;
			}

			if ($value instanceof Proxy) {
				$value = $value->__getObject();
			}

			$class = get_class($value);
			$shadow = $this->orm->getShadow($class);
			$id = $shadow->getId()->getProperty();

			if ($this->em->find($class, $value->{$id})) {
				return;
			}

			$persist = new Persist($this->connection, $this->em);
			$newValue = $persist->exec($value, $this->object);

			if ($newValue) {
				$this->object->{$property}[$key] = $newValue;
			}
		}
	}

	private function generateQuery() {
		$sql = 'INSERT INTO %s (%s) VALUES (%s)';

		$columns = [];
		$binds = [];
		$values = [];

		foreach (array_merge($this->shadow->getColumns(), $this->shadow->getJoins()) as $column) {
			if ($column instanceof Join && $column->getType() !== 'belongsTo') {
				continue;
			}

			if ($column instanceof Join && !empty($this->object->{$column->getProperty()})) {
				$class = $column->getReference();
				$reference = $this->orm->getShadow($class);
				$id = $reference->getId();
				$prop = $id->getProperty();
				$join = $this->object->{$column->getProperty()};

				if (!empty($join->$prop)) {
					$columns[] = $column->getName();
					$binds[] = ':' . $column->getName();
					$values[':' . $column->getName()] = $join->$prop;
				}
			} elseif (!empty($this->object->{$column->getProperty()}) || ($column instanceof Column && $column->isId())) {
				$columns[] = $column->getName();
				$binds[] = ':' . $column->getName();
				$values[':' . $column->getName()] = $this->object->{$column->getProperty()};
			}
		}

		$query = sprintf($sql, $this->shadow->getTableName(), implode(', ', $columns), implode(', ', $binds));
		$this->values = $values;

		return !empty($columns) ? $query : false;
	}

}
