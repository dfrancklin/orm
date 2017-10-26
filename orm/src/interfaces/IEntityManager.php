<?php

namespace ORM\Interfaces;

use ORM\Builders\Query;

interface IEntityManager {

	function __construct(\PDO $connection);

	function find(String $class, $id);

	function createQuery() : Query;

	function remove();

	function save();

	function beginTransaction();

	function commit();

	function rollback();

}
