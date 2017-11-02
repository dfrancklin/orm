<?php

namespace ORM\Builders;

use ORM\Orm;

use ORM\Core\Column;
use ORM\Core\Connection;
use ORM\Core\Join;
use ORM\Core\Shadow;

class TableManager {

	const CREATE_TABLE_TEMPLATE = 'CREATE TABLE %s (%s)';
	const DROP_TABLE_TEMPLATE = 'DROP TABLE %s%s';
	const DROP_SEQUENCE_TEMPLATE = 'DROP SEQUENCE %s';
	const ALTER_TABLE_FOREIGN = 'ALTER TABLE %s ADD CONSTRAINT %s FOREIGN KEY (%s) REFERENCES %s (%s)';

	private $orm;

	private $classes;

	private $shadows;

	private $connection;

	private $droped;

	public function __construct(Connection $connection, String $namespace, String $modelsFolder) {
		$this->orm = Orm::getInstance();
		$this->classes = $this->loadClasses($namespace, $modelsFolder);
		$this->shadows = $this->loadShadows();
		$this->connection = $connection;

		$this->droped = [];
	}

	public function drop() {
		$drops = [];

		foreach ($this->shadows as $shadow) {
			$_drops = $this->resolveDropTable($shadow);
			$drops = array_merge($drops, $_drops);
		}

		$driver = $this->connection->getDriver();

		if ($driver->GENERATE_ID_TYPE === 'SEQUENCE') {
			$_drops = $this->resolveDropSequence($driver->SEQUENCE_NAME);
			$drops = array_merge($drops, $_drops);
		}

		foreach($drops as $drop) {
			$statement = $this->connection->prepare($drop);
			$statement->execute();
		}
	}

	public function create() {
		$creates = [];
		$alters = [];

		foreach ($this->shadows as $shadow) {
			list($_creates, $_alters) = $this->resolveCreateTable($shadow);
			$creates = array_merge($creates, $_creates);
			$alters = array_merge($alters, $_alters);
		}

		foreach($creates as $create) {
			$statement = $this->connection->prepare($create);
			$statement->execute();
		}

		foreach($alters as $alter) {
			$statement = $this->connection->prepare($alter);
			$statement->execute();
		}
	}

	private function resolveCreateTable(Shadow $shadow) : Array {
		$creates = [];
		$alters = [];
		$columns = [];

		foreach ($shadow->getColumns() as $column) {
			$columns[] = $this->resolveCreateColumn($column);
		}

		foreach ($shadow->getJoins('type', 'belongsTo') as $join) {
			$reference = $join->getReference();

			if (!isset($this->shadows[$reference])) {
				$this->shadows[$reference] = $this->orm->getShadow($reference);
			}

			$reference = $this->shadows[$reference];
			$id = $reference->getId();

			$columns[] = $this->resolveCreateColumn($id, $join);
			$alters[] = $this->resolveAlterTable($shadow->getTableName(), $reference, $join->getName());
		}

		$creates[] = sprintf(self::CREATE_TABLE_TEMPLATE, $shadow->getTableName(), implode(', ', $columns));

		foreach ($shadow->getJoins('type', 'manyToMany') as $join) {
			if (empty($join->getMappedBy())) {
				list($create, $_alters) = $this->resolveJoinTable($shadow, $join);
				$creates[] = $create;
				$alters = array_merge($alters, $_alters);
			}
		}

		return [$creates, $alters];
	}

	private function resolveCreateColumn(Column $column, Join $join=null, String $name=null) : String {
		$driver = $this->connection->getDriver();

		if (empty($join) && empty($name)) {
			$definition = $column->getName();
		} elseif (!empty($join) && empty($name)) {
			$definition = $join->getName();
		} else {
			$definition = $name;
		}

		if (!($column->isId() && $driver->IGNORE_ID_DATA_TYPE && empty($join) && empty($name))) {
			$type = $column->getType();

			if (!array_key_exists($type, $driver->DATA_TYPES)) {
				throw new \Exception('The type "' . $type . '" is not supported on the "' . $driver::NAME . '" driver with the version ' . $driver::VERSION);
			}

			$dataType = $driver->DATA_TYPES[$type];

			if (substr_count($dataType, '%d') === 1) {
				$dataType = sprintf($dataType, $column->getLength());
			} elseif (substr_count($dataType, '%d') === 2) {
				$dataType = sprintf($dataType, $column->getScale(), $column->getPrecision());
			}

			$definition .= ' ' . $dataType;
		} elseif ($column->isId() && $driver->IGNORE_ID_DATA_TYPE && empty($join) && empty($name)) {
			$definition .= ' ' . $driver->GENERATE_ID_ATTR;
		}

		if (empty($join) && empty($name)) {
			$definition .= ' ' . ($column->isNullable() ? 'NULL' : 'NOT NULL');
		} elseif (!empty($join) && empty($name)) {
			$definition .= ' ' . ($join->isOptional() ? 'NULL' : 'NOT NULL');
		} else {
			$definition .= ' NOT NULL';
		}

		if (empty($join) && empty($name)) {
			if ($column->isUnique() && !$column->isId()) {
				$definition .= ' UNIQUE';
			}

			if ($column->isId()) {
				$definition .= ' PRIMARY KEY';
			}

			if ($column->isGenerated() && $driver->GENERATE_ID_TYPE === 'ATTR' && !$driver->IGNORE_ID_DATA_TYPE) {
				$definition .= ' ' . $driver->GENERATE_ID_ATTR;
			}
		}

		return "\n\t" . $definition;
	}

	private function resolveJoinTable(Shadow $shadow, Join $join) : Array {
		$referenceClass = $join->getReference();

		if (!isset($this->shadows[$referenceClass])) {
			$this->shadows[$referenceClass] = $this->orm->getShadow($referenceClass);
		}

		$reference = $this->shadows[$referenceClass];
		$joinTable = $join->getJoinTable();

		$columns[] = $this->resolveCreateColumn($shadow->getId(), null, $joinTable->getJoinColumnName());
		$columns[] = $this->resolveCreateColumn($reference->getId(), null, $joinTable->getInverseJoinColumnName());

		$create = sprintf(self::CREATE_TABLE_TEMPLATE, $joinTable->getTableName(), implode(', ', $columns));

		$alters[] = $this->resolveAlterTable($joinTable->getTableName(), $shadow, $joinTable->getJoinColumnName());
		$alters[] = $this->resolveAlterTable($joinTable->getTableName(), $reference, $joinTable->getInverseJoinColumnName());

		return [$create, $alters];
	}

	private function resolveAlterTable(String $tableName, Shadow $reference, String $columnName) : String {
		$referenceTableName = $reference->getTableName();
		$v = ['a', 'e', 'i', 'o', 'u'];

		$fk = 'FK__';
		$fk .= str_replace($v, '', $tableName);
		$fk .= '__';
		$fk .= str_replace($v, '', $referenceTableName);

		return sprintf(self::ALTER_TABLE_FOREIGN, $tableName, $fk, $columnName, $referenceTableName, $reference->getId()->getName());
	}

	private function resolveDropTable(Shadow $shadow, Join $join=null) : Array {
		if (in_array($shadow->getClass(), $this->droped)) {
			return [];
		}

		$driver = $this->connection->getDriver();
		$ifExists = $driver->SUPPORTS_IF_EXISTS ? 'IF EXISTS ' : null;
		$this->droped[] = $shadow->getClass();
		$drops = [];

		foreach ($shadow->getJoins() as $_join) {
			if ($_join->getType() === 'belongsTo') {
				continue;
			}

			$reference = $_join->getReference();

			if (in_array($reference, $this->droped)) {
				continue;
			}

			if (!isset($this->shadows[$reference])) {
				$this->shadows[$reference] = $this->orm->getShadow($reference);
			}

			$_drops = $this->resolveDropTable($this->shadows[$reference], $_join);
			$drops = array_merge($drops, $_drops);
		}

		$joinTable = null;

		if ($join && $join->getType() === 'manyToMany') {
			if (empty($join->getMappedBy())) {
				$joinTable = $join->getJoinTable();
			} else {
				$reference = $join->getReference();

				if (!isset($this->shadows[$reference])) {
					$this->shadows[$reference] = $this->orm->getShadow($reference);
				}

				$_shadow = $this->shadows[$reference];
				$_joins = $_shadow->getJoins('property', $join->getMappedBy());

				if (!empty($_joins) && count($_joins) === 1) {
					$_join = $_joins[0];
					$joinTable = $_join->getJoinTable();
				}
			}
		}

		if ($joinTable) {
			$drops[] = sprintf(self::DROP_TABLE_TEMPLATE, $ifExists, $joinTable->getTableName());
		}

		$drops[] = sprintf(self::DROP_TABLE_TEMPLATE, $ifExists, $shadow->getTableName());

		return $drops;
	}

	private function resolveDropSequence($sequenceName) : Array {
		return [sprintf(self::DROP_SEQUENCE_TEMPLATE, $sequenceName)];
	}

	private function loadClasses($namespace, $folder) : Array {
		$ds = DIRECTORY_SEPARATOR;
		$classes = [];

		foreach (scandir($folder) as $file) {
			if (in_array($file, ['.', '..'])) {
				continue;
			}

			$file = explode('.', $file);
			$extension = array_pop($file);
			$file = implode('.', $file);

			if ($extension !== 'php') {
				continue;
			}

			$classes[] = $namespace . '\\' . $file;
		}

		return $classes;
	}

	private function loadShadows() : Array {
		$shadows = [];

		foreach ($this->classes as $class) {
			$shadows[$class] = $this->orm->getShadow($class);
		}

		return $shadows;
	}

}
