<?php

namespace ORM\Core;

use ORM\Orm;

use ORM\Mappers\Join;

use ORM\Interfaces\IEntityManager;

class Proxy
{

	private $em;

	private $orm;

	private $object;

	private $shadow;

	private $values;

	public function __construct(IEntityManager $em, $object, Array $values)
	{
		$this->orm = Orm::getInstance();
		$this->em = $em;
		$this->object = $object;
		$this->values = $values;
		$this->shadow = $this->orm->getShadow(get_class($object));
	}

	public function __get(String $property)
	{
		if (!property_exists($this->shadow->getClass(), $property)) {
			throw new \Exception('The property "' . $property . '" does not exists on class "' . $this->shadow->getClass() . '"');
		}

		$joins = $this->shadow->getJoins('property', $property);

		if (!empty($joins) && count($joins) === 1) {
			$newValue = $this->lazy($joins[0], $property);

			if ($newValue) {
				$this->object->{$property} = $newValue;
			}
		}

		return $this->object->{$property};
	}

	public function __set(String $property, $value)
	{
		if (!property_exists($this->shadow->getClass(), $property)) {
			throw new \Exception('The property "' . $property . '" does not exists on class "' . $this->shadow->getClass() . '"');
		}

		$this->object->{$property} = $value;
	}

	public function __call(String $method, Array $arguments)
	{
		if (!method_exists($this->shadow->getClass(), $method)) {
			throw new \Exception('The method "' . $method . '" does not exists on class "' . $this->shadow->getClass() . '"');
		}

		return $this->object->{$method}(...$arguments);
	}

	public function __isset(String $name) : bool
	{
		return isset($this->object->{$name});
	}

	public function __unset(String $name)
	{
		unset($this->object->{$name});
	}

	private function lazy(Join $join, String $property)
	{
		if (!is_null($this->object->{$property})) {
			return $this->object->{$property};
		}

		$method = 'lazy' . ucfirst($join->getType());

		return $this->$method($join, $property);
	}

	private function lazyHasOne(Join $join)
	{
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

			return $rs;
		}

		return false;
	}

	private function lazyHasMany(Join $join)
	{
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

			return $rs;
		}

		return false;
	}

	private function lazyManyToMany(Join $join)
	{
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

		return $rs;
	}

	private function lazyBelongsTo(Join $join)
	{
		if (!array_key_exists($join->getProperty(), $this->values)) {
			return false;
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

		return $rs;
	}

	public function __getObject()
	{
		return $this->object;
	}

	public function __setObject($object)
	{
		$this->object = $object;
	}

}
