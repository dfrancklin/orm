<?php

namespace ORM\Core;

use ORM\Orm;
use ORM\Builders\Query;

use ORM\Interfaces\IEntityManager;

class EntityManager implements IEntityManager {

	private $connection;

	private $transactionActive;

	public function __construct(\PDO $connection) {
		$this->connection = $connection;
		$this->orm = Orm::getInstance();
	}

	public function find(String $class, $id) {
		$shadow = $this->orm->getShadow($class);
		$alias = strtolower($shadow->getTableName()[0]);
		$prop = $alias . '.' . $shadow->getId()->getProperty();

		$query = $this->createQuery();

		return $query->from($class, $alias)->where($prop)->equals($id)->one();
	}

	public function createQuery() : Query {
		return new Query($this->connection, $this);
	}

	public function remove($id) {

	}

	public function save($object) {
		if (!$this->transactionActive) {
			throw new \Exception('A transaction must be active in order to save an object');
		}

		if (empty($object)) {
			throw new \Exception('A valid object must be passed as parameter');
		}

		$class = get_class($object);
		$shadow = $this->orm->getShadow($class);
		$id = $shadow->getId();
		$prop = $id->getProperty();

		if (!empty($object->$prop)) {
			$method = 'update';
		} else {
			$method = 'persist';
		}

		return $this->$method($object, $shadow);
	}

	public function beginTransaction() {
		if ($this->transactionActive) {
			throw new \Exception('A transaction is already active');
		}

		if ($this->connection->beginTransaction()) {
			$this->transactionActive = true;
			return true;
		}

		throw new \Exception('Something went wrong while beginning a transaction');
	}

	public function commit() {
		if (!$this->transactionActive) {
			throw new \Exception('A transaction must be active in order to commit');
		}

		if ($this->connection->commit()) {
			$this->transactionActive = false;
			return true;
		}

		throw new \Exception('Something went wrong while committing a transaction');
	}

	public function rollback() {
		if (!$this->transactionActive) {
			throw new \Exception('A transaction must be active in order to rollback');
		}

		if ($this->connection->rollback()) {
			$this->transactionActive = false;
			return true;
		}

		throw new \Exception('Something went wrong while rolling back a transaction');
	}

	private function persist($object, $shadow) {
		if ($this->exists($object, $shadow)) {
			return $this->update($object, $shadow);
		}

		vd('create persist builder instance');
	}

	private function update($object, $shadow) {
		if (!$this->exists($object, $shadow)) {
			return $this->persist($object, $shadow);
		}

		vd('create update builder instance');
	}

	private function exists($object, $shadow) {
		$id = $shadow->getId();
		$prop = $id->getProperty();
		$rs = $this->find(get_class($object), $object->$prop);

		return !!$rs;
	}

}
