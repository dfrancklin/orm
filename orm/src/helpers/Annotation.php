<?php

namespace ORM\Core;

use ORM\Orm;

use ORM\Constants\CascadeTypes;

use ORM\Mappers\Shadow;

class Annotation
{

	private $orm;

	private $class;

	private $shadow;

	private $resolver;

	private $reflect;

	public function __construct(String $class)
	{
		$this->class = $class;
		$this->orm = Orm::getInstance();
		$this->shadow = new Shadow($class);
		$this->resolver = new ExpressionResolver();
		$this->reflect = new \ReflectionClass($class);
	}

	public function mapper() : Shadow
	{
		$class = $this->resolveClass();

		foreach($this->reflect->getProperties() as $property) {
			$this->resolveProperty($property);
		}

		return $this->shadow;
	}

	private function resolveClass()
	{
		if (!($class = $this->resolver->get('orm', $this->reflect->getDocComment(), true))) {
			throw new \Exception("A classe \"$this->class\" não está devidamente anotada");
		}

		if (!$this->resolver->get('entity', $class)) {
			throw new \Exception("Está faltando a anotação \"Entity\" na classe \"$this->class\"");
		}

		$table = $this->resolver->get('table', $class);
		$name = $this->resolver->get('name', $table);

		if (!$table || !$name) {
			$c = explode('\\', $this->class);
			$name = strtolower(end($c));
		}

		$this->shadow->setTableName($name);

		$schema = $this->resolver->get('schema', $table);
		$this->shadow->setSchema($schema);

		if ($mutable = $this->resolver->get('mutable', $table)) {
			$this->shadow->setMutable($mutable === 'true');
		}
	}

	private function resolveProperty(\ReflectionProperty $property)
	{
		$prop = $this->resolver->get('orm', $property->getDocComment(), true);

		if ($this->resolver->get('transient', $prop)) return;

		if ($this->resolver->get('hasOne', $prop)) {
			$this->resolveJoin($property, 'hasOne');
		} elseif ($this->resolver->get('hasMany', $prop)) {
			$this->resolveJoin($property, 'hasMany');
		} elseif ($this->resolver->get('manyToMany', $prop)) {
			$this->resolveJoin($property, 'manyToMany');
		} elseif ($this->resolver->get('belongsTo', $prop)) {
			$this->resolveJoin($property, 'belongsTo');
		} else {
			$this->resolveColumn($property);
		}
	}

	private function resolveJoin(\ReflectionProperty $property, String $type)
	{
		$join = new Join();
		$prop = $this->resolver->get('orm', $property->getDocComment(), true);

		if ($has = $this->resolver->get($type, $prop)) {
			$reference = $this->resolver->get('className', $has);

			if (!$reference) {
				throw new \Exception('É obrigatório informar a classe de referência');
			}

			if (!class_exists($reference)) {
				throw new \Exception("A classe \"$reference\" não existe");
			}

			$join->setReference($reference);
		}

		$join->setProperty($property->getName());
		$join->setType($type);

		if ($cascade = $this->resolver->get('cascade', $has)) {
			$cascade = preg_split("/,\s?/i", $cascade);

			if(in_array('ALL', $cascade)) {
				$cascade = CascadeTypes::TYPES;
			}

			foreach ($cascade as $c) {
				if (!in_array($c, CascadeTypes::TYPES)) {
					throw new \Exception('Cascade type "' . $c . '" does not exists');
				}
			}

			$join->setCascade($cascade);
		}

		if ($optional = $this->resolver->get('optional', $has)) {
			$join->setOptional($optional === 'true');
		}

		if ($type === 'manyToMany') {
			if ($mappedBy = $this->resolver->get('mappedBy', $has)) {
				$join->setMappedBy($mappedBy);
			} else {
				$table = new JoinTable();
				$reference = $this->orm->getShadow($join->getReference());

				if ($joinTable = $this->resolver->get('joinTable', $prop)) {
					if ($tableName = $this->resolver->get('tableName', $joinTable)) {
						$table->setTableName($tableName);
					} else {
						$tableName = $this->shadow->getTableName() . '_' . $reference->getTableName();
						$table->setTableName($tableName);
					}

					$schema = $this->resolver->get('schema', $joinTable);
					$table->setSchema($schema);

					if ($joinColumn = $this->resolver->get('join', $joinTable)) {
						$name = $this->resolver->get('name', $joinColumn);
					} else {
						$name = $this->shadow->getTableName() . '_' . $this->shadow->getId()->getName();
					}

					$table->setJoinColumnName($name);

					if ($inverseJoinColumn = $this->resolver->get('inverse', $joinTable)) {
						$name = $this->resolver->get('name', $inverseJoinColumn);
					} else {
						$name = $reference->getTableName() . '_' . $reference->getId()->getName();
					}

					$table->setInverseJoinColumnName($name);
				} else {
					$tableName = $this->shadow->getTableName() . '_' . $reference->getTableName();
					$table->setTableName($tableName);
					$joinName = $this->shadow->getTableName() . '_' . $this->shadow->getId()->getName();
					$table->setJoinColumnName($joinName);
					$inverseName = $reference->getTableName() . '_' . $reference->getId()->getName();
					$table->setInverseJoinColumnName($inverseName);
				}

				$join->setJoinTable($table);
			}
		} elseif ($type === 'belongsTo') {
			if ($column = $this->resolver->get('joinColumn', $prop)) {
				$name = $this->resolver->get('name', $column);
				$join->setName($name);
			}

			if (!$join->getName()) {
				$id = $this->shadow->getId()->getName();
				$join->setName($property->getName() . '_' . $id);
			}
		}

		$this->shadow->addJoin($join);
	}

	private function resolveColumn(\ReflectionProperty $property)
	{
		$shadowColumn = new Column();
		$prop = $this->resolver->get('orm', $property->getDocComment(), true);

		if ($id = $this->resolver->get('id', $prop)) {
			$shadowColumn->setId(!!$id);
		}

		if ($generated = $this->resolver->get('generated', $prop)) {
			$shadowColumn->setGenerated(!!$generated);
		}

		if ($column = $this->resolver->get('column', $prop)) {
			if ($name = $this->resolver->get('name', $column)) {
				$shadowColumn->setName($name);
			} else {
				$shadowColumn->setName($property->getName());
			}

			if ($type = $this->resolver->get('type', $column)) {
				$shadowColumn->setType($type);
			}

			if ($length = $this->resolver->get('length', $column)) {
				$shadowColumn->setLength((int) $length);
			}

			if ($scale = $this->resolver->get('scale', $column)) {
				$shadowColumn->setScale((int) $scale);
			}

			if ($precision = $this->resolver->get('precision', $column)) {
				$shadowColumn->setPrecision((int) $precision);
			}

			if ($unique = $this->resolver->get('unique', $column)) {
				$unique = !is_null($unique) && $unique === 'true' && !$id;
				$shadowColumn->setUnique($unique);
			}

			if ($nullable = $this->resolver->get('nullable', $column)) {
				$shadowColumn->setNullable($nullable === 'true' || is_null($nullable));
			}

			if ($shadowColumn->isId() || $shadowColumn->isUnique()) {
				$shadowColumn->setNullable(false);
			}
		} else {
			$shadowColumn->setName($property->getName());
		}

		$shadowColumn->setProperty($property->getName());
		$this->shadow->addColumn($shadowColumn);
	}

}
