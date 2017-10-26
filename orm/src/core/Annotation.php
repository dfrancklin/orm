<?php
namespace ORM\Core;

use ORM\Orm;

class Annotation {

	private $orm;

	private $class;

	private $shadow;

	private $resolver;

	private $reflect;

	public function __construct(Orm $orm, String $class) {
		$this->orm = $orm;
		$this->class = $class;
		$this->shadow = new Shadow($class);
		$this->resolver = new ExpressionResolver();
		$this->reflect = new \ReflectionClass($class);
	}

	public function mapper() : Shadow {
		$class = $this->resolveClass();

		foreach($this->reflect->getProperties() as $property) {
			$this->resolveProperty($property);
		}

		return $this->shadow;
	}

	private function resolveClass() {
		if (!($class = $this->resolver->get('orm', $this->reflect->getDocComment(), true)))
			throw new \Exception("A classe \"$this->class\" não está devidamente anotada");

		if (!$this->resolver->get('entity', $class))
			throw new \Exception("Está faltando a anotação \"Entity\" na classe \"$this->class\"");

		$table = $this->resolver->get('table', $class);
		$name = $this->resolver->get('name', $table);

		if (!$table || !$name) {
			$c = explode('\\', $this->class);
			$name = strtolower(end($c));
		}

		$this->shadow->setTableName($name);
	}

	private function resolveProperty($property) {
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

	private function resolveJoin($property, $type) {
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
			$all = ['INSERT', 'UPDATE', 'DELETE'];
			$cascade = preg_split("/,\s?/i", $cascade);

			if(in_array('ALL', $cascade)) {
				$cascade = $all;
			}

			foreach ($cascade as $c) {
				if (!in_array($c, $all)) {
					throw new \Exception('Cascade type "' . $c . '" does not exists');
				}
			}

			$join->setCascade($cascade);
		} else {
			$join->setCascade([]);
		}

		if ($type === 'manyToMany') {
			if ($mappedBy = $this->resolver->get('mappedBy', $has)) {
				$join->setMappedBy($mappedBy);
			} else {
				$table = new JoinTable();

				if ($joinTable = $this->resolver->get('joinTable', $prop)) {
					$table->setTableName($this->resolver->get('tableName', $joinTable));

					if ($joinColumn = $this->resolver->get('join', $joinTable)) {
						$name = $this->resolver->get('name', $joinColumn);
					} else {
						$name = $this->shadow->getTableName() . '_' . $this->shadow->getId()->getName();
					}

					$table->setJoinColumnName($name ? $name : 'id');

					if ($inverseJoinColumn = $this->resolver->get('inverse', $joinTable)) {
						$name = $this->resolver->get('name', $inverseJoinColumn);
					} else {
						$shadow = $this->orm->getShadow($join->getReference());
						$name = $shadow->getTableName() . '_' . $shadow->getId()->getName();
					}

					$table->setInverseJoinColumnName($name ? $name : 'id');
				}

				$join->setJoinTable($table);
			}
		} else {
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


	private function resolveColumn($property) {
		$shadowColumn = new Column();
		$prop = $this->resolver->get('orm', $property->getDocComment(), true);
		$id = $this->resolver->get('id', $prop);
		$shadowColumn->setId(!!$id);

		$shadowColumn->setGenerated(!!$this->resolver->get('generated', $prop));

		if ($column = $this->resolver->get('column', $prop)) {
			$name = $this->resolver->get('name', $column);
			$shadowColumn->setName($name ? $name : $property->getName());

			$shadowColumn->setType($this->resolver->get('type', $column));
			$shadowColumn->setLength($this->resolver->get('length', $column));

			$nullable = $this->resolver->get('nullable', $column);
			$shadowColumn->setNullable($nullable === 'true' || is_null($nullable) && !$id);

			$unique = $this->resolver->get('unique', $column);
			$shadowColumn->setUnique($unique === 'true' || is_null($unique) && !$id);
		}

		$shadowColumn->setProperty($property->getName());
		$this->shadow->addColumn($shadowColumn);
	}

}
