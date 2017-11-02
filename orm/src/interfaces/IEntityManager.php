<?php

namespace ORM\Interfaces;

use ORM\Core\Connection;

use ORM\Builders\Query;

interface IEntityManager {

	function __construct(Connection $connection);

	function find(String $class, $id);

	function createQuery() : Query;

	function remove($object);

	function save($object);

	function beginTransaction();

	function commit();

	function rollback();

}
