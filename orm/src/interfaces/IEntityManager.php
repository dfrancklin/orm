<?php

namespace ORM\Interfaces;

use ORM\Builders\Query;

use ORM\Interfaces\IConnection;

interface IEntityManager
{

	function __construct(IConnection $connection);

	function find(String $class, $id);

	function createQuery() : Query;

	function remove($object);

	function save($object);

	function beginTransaction() : bool;

	function commit() : bool;

	function rollback() : bool;

}
