<?php

namespace ORM\Builders;

use ORM\Orm;
use ORM\Core\Join;

use ORM\Interfaces\IEntityManager;

class Persist {

	private $em;

	private $orm;

	private $shadow;

	private $connection;

	private $query;

	// private $columns;

	// private $values;

	public function __construct(\PDO $connection, IEntityManager $em) {
		if (!$connection) {
			throw new \Exception('Conexão não definida');
		}

		$this->orm = Orm::getInstance();
		$this->em = $em;
		$this->connection = $connection;
	}

	public function exec($object) {
		$this->shadow = $this->orm->getShadow(get_class($object));

		$this->persistBefore($object);

		if (!$this->generateQuery($object)) {
			throw new \Exception('The object of the class "' . $this->shadow->getClass() . '" seems to be empty');
		}

		$statement = $this->connection->prepare($this->query);
		/*
		$executed = $statement->execute($this->values);
		$lastId = $this->connection->lastInsertId();
		*/
		vd($statement, $this->values);

		$this->persistAfter($object);
		// die();

		return $object;
	}

	private function persistBefore($object) {
		foreach ($this->shadow->getJoins() as $join) {
			if ($join->getType() === 'belongsTo' &&
					in_array('INSERT', $join->getCascade()) &&
					!empty($object->{$join->getProperty()})) {
				$value = $object->{$join->getProperty()};
				$persist = new self($this->connection, $this->em);
				$object->{$join->getProperty()} = $persist->exec($value);
			}
		}
	}

	private function persistAfter($object) {

	}

	private function generateQuery($object) {
		$sql = 'INSERT INTO %s (%s) VALUES (%s)';

		$columns = [];
		$binds = [];
		$values = [];

		foreach (array_merge($this->shadow->getColumns(), $this->shadow->getJoins()) as $column) {
			if ($column instanceof Join && $column->getType() !== 'belongsTo') {
				continue;
			}

			if ($column instanceof Join && !empty($object->{$column->getProperty()})) {
				$class = $column->getReference();
				$reference = $this->orm->getShadow($class);
				$id = $reference->getId();
				$prop = $id->getProperty();
				$join = $object->{$column->getProperty()};

				if (!empty($join->$prop)) {
					$columns[] = $column->getName();
					$binds[] = ':' . $column->getName();
					$values[':' . $column->getName()] = $join->$prop;
				}
			} elseif (!empty($object->{$column->getProperty()})) {
				$columns[] = $column->getName();
				$binds[] = ':' . $column->getName();
				$values[':' . $column->getName()] = $object->{$column->getProperty()};
			}
		}

		$this->query = sprintf($sql, $this->shadow->getTableName(), implode(', ', $columns), implode(', ', $binds));
		$this->values = $values;

		return !empty($columns);
	}

}
