<?php

namespace ORM\Builders;

use ORM\Orm;
use ORM\Core\Proxy;

use ORM\Interfaces\IEntityManager;

class Merge {

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
		$id = $this->shadow->getId()->getProperty();

		if (!$this->generateQuery()) {
			throw new \Exception('The object of the class "' . $this->shadow->getClass() . '" seems to be empty');
		}

		// $statement = $this->connection->prepare($this->query);
		// $executed = $statement->execute($this->values);

		// if (!$statement->rowCount()) {
		// 	throw new \Exception('Something went wrong while updatting a transaction');
		// }

		if ($proxy) {
			$proxy->__setObject($object);
			$object = $proxy;
		}

		return $this->object;
	}

	private function generateQuery() {
		$sql = 'UPDATE %s SET %s WHERE %s = %s';

		$query = sprintf($sql, 'table', 'field = :value', 'id', ':id');
		vd($query);
		die();

		return true;
	}
}
