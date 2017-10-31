<?php

namespace ORM\Builders;

use ORM\Orm;
use ORM\Core\Proxy;

use ORM\Interfaces\IEntityManager;

class Remove {

	private $em;

	private $orm;

	private $proxy;

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
		$this->proxy = $proxy;
		$this->object = $object;
		$this->original = $original ?? $object;
		$this->shadow = $this->orm->getShadow($class);

		if (!$this->proxy) {
			foreach ($this->shadow->getJoins('type', 'belongsTo') as $join) {
				$property = $join->getProperty();
				$values[$property] = $this->object->{$property};
			}

			$this->proxy = new Proxy($this->em, $this->object, $values);
		}

		$rows = 0;

		$rows += $this->removeManyToMany();
		$rows += $this->removeBefore();

		if (!($query = $this->generateQuery())) {
			throw new \Exception('The object of the class "' . $this->shadow->getClass() . '" seems to be empty');
		}

		vd($query, $this->values);

		$statement = $this->connection->prepare($query);
		$executed = $statement->execute($this->values);

		if (!($rowCount = $statement->rowCount())) {
			throw new \Exception('Something went wrong while removing a register');
		}

		$rows += $rowCount;

		$rows += $this->removeAfter();

		return $rows;
	}

	private function removeManyToMany() {
		$rows = 0;

		foreach ($this->shadow->getJoins('type', 'manyToMany') as $join) {
			$property = $join->getProperty();

			if (in_array('DELETE', $join->getCascade())) {
				$rows += $this->removeManyCascade($join);
			}

			$this->deleteManyToMany($join);
		}

		return $rows;
	}

	private function deleteManyToMany($join) {
		$reference = $this->orm->getShadow($join->getReference());
		$property = $join->getProperty();
		$joinTable = null;
		$column = null;
		$id = null;

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
			$column = $joinTable->getInverseJoinColumnName();
			$id = $reference->getId()->getProperty();
		} else {
			$joinTable = $join->getJoinTable();
			$column = $joinTable->getJoinColumnName();
			$id = $this->shadow->getId()->getProperty();
		}

		$template = 'DELETE FROM %s WHERE %s = %s';
		$table = $joinTable->getTableName();
		$bind = ':' . $column;
		$values[$bind] = $this->object->{$id};

		$sql = sprintf($template, $table, $column, $bind);

		$statement = $this->connection->prepare($sql);
		$statement->execute($values);
	}

	private function removeBefore() {
		$rows = 0;

		foreach ($this->shadow->getJoins('type', 'hasOne') as $join) {
			if (in_array('DELETE', $join->getCascade())) {
				$rows += $this->removeCascade($join);
			}
		}

		foreach ($this->shadow->getJoins('type', 'hasMany') as $join) {
			if (in_array('DELETE', $join->getCascade())) {
				$rows += $this->removeManyCascade($join);
			}
		}

		return $rows;
	}

	private function removeAfter() {
		$rows = 0;

		foreach ($this->shadow->getJoins('type', 'belongsTo') as $join) {
			if (in_array('DELETE', $join->getCascade())) {
				$rows += $this->removeCascade($join);
			}
		}

		return $rows;
	}

	private function removeCascade($join) {
		$property = $join->getProperty();
		$value = $this->proxy->{$property};

		return $this->_remove($join, $value);
	}

	private function removeManyCascade($join) {
		$property = $join->getProperty();
		$values = $this->proxy->{$property};

		if (!is_array($values)) {
			return;
		}

		$rows = 0;

		foreach($values as $key => $value) {
			$rows += $this->_remove($join, $value);
		}

		return $rows;
	}

	private function _remove($join, $value) {
		if (!is_object($value)) {
			return 0;
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

		if (!$this->em->find($class, $value->{$id})) {
			return 0;
		}

		$remove = new Remove($this->connection, $this->em);
		$rows = $remove->exec($proxy ?? $value, $this->original);

		return $rows;
	}

	private function generateQuery() {
		$sql = 'DELETE FROM %s WHERE %s = %s';

		$idName = $idBind = null;
		$values = [];

		foreach ($this->shadow->getColumns() as $column) {
			if ($column->isId()) {
				$idName = $column->getProperty();
				$idBind = ':' . $column->getProperty();
				$values[$idBind] = $this->object->{$column->getProperty()};

				break;
			}
		}

		$query = sprintf($sql, $this->shadow->getTableName(), $idName, $idBind);
		$this->values = $values;

		return ($idName && $idBind) ? $query : false;
	}

}
