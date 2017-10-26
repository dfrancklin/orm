<?php

namespace ORM\Core;

use ORM\Orm;

class Proxy {

	private $orm;

	private $object;

	private $shadow;

	private $values;

	public function __construct($object, $shadow, $values) {
		$this->orm = Orm::getInstance();
		$this->object = $object;
		$this->shadow = $shadow;
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

		return $this->object->{$method}($arguments);
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
			$id = $this->shadow->getId();
			$value = $this->object->{$id->getProperty()};
			$query = $this->orm->createQuery();

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
			$id = $this->shadow->getId();
			$value = $this->object->{$id->getProperty()};
			$query = $this->orm->createQuery();

			$rs = $query->from($class, $alias)
					->where($prop)->equals($value)
					->all();

			$this->object->{$join->getProperty()} = $rs;

			return $this->object->{$join->getProperty()};
		}
	}

	private function lazyManyToMany(Join $join) {
		$class = $join->getReference();
		$j = $join->getShadow()->getClass();

		$prop = '_y.' . $join->getShadow()->getId()->getProperty();
		$id = $this->shadow->getId();
		$value = $this->object->{$id->getProperty()};

		$query = $this->orm->createQuery();

		$rs = $query->from($class, '_x')
				->join($j, '_y')
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
		$id = $this->shadow->getId();
		$prop = $alias . '.' . $id->getProperty();
		$value = $this->values[$join->getProperty()];

		$query = $this->orm->createQuery();

		$rs = $query->from($class, $alias)
				->where($prop)->equals($value)
				->one();

		$this->object->{$join->getProperty()} = $rs;

		return $this->object->{$join->getProperty()};
	}

}
