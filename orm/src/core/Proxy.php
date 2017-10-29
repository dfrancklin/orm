<?php

namespace ORM\Core;

use ORM\Orm;

class Proxy {

	private $em;

	private $orm;

	private $object;

	private $shadow;

	private $values;

	public function __construct($em, $object, $values) {
		$this->orm = Orm::getInstance();
		$this->em = $em;
		$this->object = $object;
		$this->shadow = $this->orm->getShadow(get_class($object));
		$this->values = $values;
	}

	public function __get($property) {
		if (!property_exists($this->shadow->getClass(), $property)) {
			throw new \Exception('The property "' . $property . '" does not exists on class "' . $this->shadow->getClass() . '"');
		}

		$joins = $this->shadow->getJoins('property', $property);

		if (!empty($joins) && count($joins) === 1) {
			return $this->lazy($joins[0], $property);
		}

		return $this->object->{$property};
	}

	public function __set($property, $value) {
		if (!property_exists($this->shadow->getClass(), $property)) {
			throw new \Exception('The property "' . $property . '" does not exists on class "' . $this->shadow->getClass() . '"');
		}

		$this->object->{$property} = $value;
	}

	public function __call($method, $arguments) {
		if (!method_exists($this->shadow->getClass(), $method)) {
			throw new \Exception('The method "' . $method . '" does not exists on class "' . $this->shadow->getClass() . '"');
		}

		return $this->object->{$method}(...$arguments);
	}

	private function lazy(Join $join, String $property) {
		if (!is_null($this->object->{$property})) {
			return $this->object->{$property};
		}

		$method = 'lazy' . ucfirst($join->getType());

		return $this->$method($join, $property);
	}

	private function lazyHasOne(Join $join) {
		$class = $join->getReference();
		$reference = $this->orm->getShadow($class);
		$referenceJoins = $reference->getJoins('reference', $this->shadow->getClass());

		$foundedJoins = array_filter($referenceJoins, function($join) {
			return $join->getType() === 'belongsTo';
		});
		$foundedJoins = array_values($foundedJoins);

		if (!empty($foundedJoins) && count($foundedJoins) === 1) {
			$join = $foundedJoins[0];
			$alias = strtolower($reference->getTableName()[0]);
			$prop = $alias . '.' . $join->getProperty();
			$id = $this->shadow->getId()->getProperty();
			$value = $this->object->{$id};
			$query = $this->em->createQuery();

			$rs = $query->from($class, $alias)
					->where($prop)->equals($value)
					->one();

			$this->object->{$join->getProperty()} = $rs;

			return $this->object->{$join->getProperty()};
		}
	}

	private function lazyHasMany(Join $join) {
		$class = $join->getReference();
		$reference = $this->orm->getShadow($class);
		$referenceJoins = $reference->getJoins('reference', $this->shadow->getClass());

		$foundedJoins = array_filter($referenceJoins, function($join) {
			return $join->getType() === 'belongsTo';
		});
		$foundedJoins = array_values($foundedJoins);

		if (!empty($foundedJoins) && count($foundedJoins) === 1) {
			$join = $foundedJoins[0];
			$alias = strtolower($reference->getTableName()[0]);
			$prop = $alias . '.' . $join->getProperty();
			$id = $this->shadow->getId()->getProperty();
			$value = $this->object->{$id};
			$query = $this->em->createQuery();

			$rs = $query->distinct()
					->from($class, $alias)
					->where($prop)->equals($value)
					->all();

			$this->object->{$join->getProperty()} = $rs;

			return $this->object->{$join->getProperty()};
		}
	}

	private function lazyManyToMany(Join $join) {
		$class = $join->getReference();
		$alias = '_x';
		$joinClass = $join->getShadow()->getClass();
		$joinAlias = '_y';
		$prop = $joinAlias . '.' . $join->getShadow()->getId()->getProperty();
		$value = $this->object->{$this->shadow->getId()->getProperty()};

		$query = $this->em->createQuery();


		$rs = $query->distinct()
				->from($class, $alias)
				->join($joinClass, $joinAlias)
				->where($prop)->equals($value)
				->all();

		$this->object->{$join->getProperty()} = $rs;

		return $this->object->{$join->getProperty()};
	}

	private function lazyBelongsTo(Join $join) {
		if (!array_key_exists($join->getProperty(), $this->values)) {
			return;
		}

		$class = $join->getReference();
		$reference = $this->orm->getShadow($class);
		$alias = strtolower($reference->getTableName()[0]);
		$id = $this->shadow->getId()->getProperty();
		$prop = $alias . '.' . $id;
		$value = $this->values[$join->getProperty()];

		$query = $this->em->createQuery();

		$rs = $query->from($class, $alias)
				->where($prop)->equals($value)
				->one();

		$this->object->{$join->getProperty()} = $rs;

		return $this->object->{$join->getProperty()};
	}

	public function __getObject() {
		return $this->object;
	}

	public function __setObject($object) {
		$this->object = $object;
	}

}
