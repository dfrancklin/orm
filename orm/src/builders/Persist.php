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
		$this->original = $original ?? $object;
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
			throw new \Exception('Something went wrong while persistting a register');
		}

		$lastId = $this->connection->lastInsertId();

		if ($column->getType() === 'int') {
			$this->object->{$id} = (int) $lastId;
		} else {
			$this->object->{$id} = $lastId;
		}

		$this->persistManyToMany();
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

	private function persistManyToMany() {
		foreach ($this->shadow->getJoins('type', 'manyToMany') as $join) {
			$property = $join->getProperty();

			if (in_array('INSERT', $join->getCascade()) &&
					!empty($this->object->{$property})) {
				$this->persistManyCascade($join);
			}

			if (empty($join->getMappedBy()) &&
					!empty($this->object->{$property})) {
				$this->insertManyToMany($join);
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
		foreach ($this->shadow->getJoins('type', 'belongsTo') as $join) {
			if (in_array('INSERT', $join->getCascade())) {
				$this->persistCascade($join);
			}
		}
	}

	private function persistAfter() {
		foreach ($this->shadow->getJoins('type', 'hasOne') as $join) {
			if (in_array('INSERT', $join->getCascade())) {
				$this->persistCascade($join);
			}
		}

		foreach ($this->shadow->getJoins('type', 'hasMany') as $join) {
			if (in_array('INSERT', $join->getCascade())) {
				$this->persistManyCascade($join);
			}
		}
	}

	private function persistCascade($join) {
		$property = $join->getProperty();
		$value = $this->object->{$property};
		$this->object->{$property} = $this->_persist($join, $value);
	}

	private function persistManyCascade($join) {
		$property = $join->getProperty();
		$values = $this->object->{$property};

		if (!is_array($values)) {
			return;
		}

		foreach($values as $key => $value) {
			$this->object->{$property}[$key] = $this->_persist($join, $value);
		}
	}

	private function _persist($join, $value) {
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
		$builder = Persist::class;

		if ($this->em->find($class, $value->{$id})) {
			if (in_array('UPDATE', $join->getCascade())) {
				$builder = Merge::class;
			} else {
				if ($proxy) {
					$proxy->__setObject($value);
					$value = $proxy;
				}

				return $value;
			}
		}

		$builder = new $builder($this->connection, $this->em);
		$newValue = $builder->exec($value, $this->original);

		if ($newValue) {
			if ($proxy) {
				$proxy->__setObject($newValue);
				$newValue = $proxy;
			}

			return $newValue;
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
				$value = $this->object->{$column->getProperty()};
				$values[':' . $column->getName()] = $this->convertValue($value, $column->getType());
			}
		}

		$query = sprintf($sql, $this->shadow->getTableName(), implode(', ', $columns), implode(', ', $binds));
		$this->values = $values;

		return !empty($columns) ? $query : false;
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
