<?php

namespace ORM\Interfaces;

interface IConnection {

	public function prepare(String $sql) : \PDOStatement;

	public function lastInsertId() : String;

	public function beginTransaction() : bool;

	public function commit() : bool;

	public function rollback() : bool;

	public function getDriver();

}
