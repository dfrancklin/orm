<?php

namespace ORM\Core;

use ORM\Core\Driver;

use ORM\Interfaces\IConnection;

class Connection implements IConnection {

	private $pdo;

	private $driver;

	public function __construct(\PDO $pdo, Driver $driver) {
		$this->pdo = $pdo;
		$this->driver = $driver;
	}

	public function prepare(String $sql) : \PDOStatement {
		return $this->pdo->prepare($sql);
	}

	public function lastInsertId() : String {
		return $this->pdo->lastInsertId();
	}

	public function beginTransaction() : bool {
		return $this->pdo->beginTransaction();
	}

	public function commit() : bool {
		return $this->pdo->commit();
	}

	public function rollback() : bool {
		return $this->pdo->rollback();
	}

	public function getDriver() {
		return $this->driver;
	}

}
