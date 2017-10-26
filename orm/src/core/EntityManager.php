<?php

namespace ORM\Core;

use ORM\Builders\Query;

use ORM\Interfaces\IEntityManager;

class EntityManager implements IEntityManager {

	private $connection;

	public function __construct(\PDO $connection) {
		$this->connection = $connection;
	}

	public function find(String $class, $id) {
		return new Query($this->connection);
	}

	public function createQuery() : Query {
		return new Query($this->connection, $this);
	}

	public function remove() {

	}

	public function save() {

	}

	public function beginTransaction() {

	}

	public function commit() {

	}

	public function rollback() {

	}

}
