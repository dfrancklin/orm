<?php

namespace ORM\Builders;

use ORM\Orm;
use ORM\Core\Driver;
use ORM\Core\Proxy;

use ORM\Builders\Handlers\AggregateHandler;
use ORM\Builders\Handlers\GroupByHandler;
use ORM\Builders\Handlers\HavingHandler;
use ORM\Builders\Handlers\JoinHandler;
use ORM\Builders\Handlers\OperatorHandler;
use ORM\Builders\Handlers\OrderByHandler;
use ORM\Builders\Handlers\WhereHandler;

use ORM\Interfaces\IEntityManager;

class Query {

	use AggregateHandler, GroupByHandler, HavingHandler, JoinHandler, OperatorHandler, OrderByHandler, WhereHandler;

	private $orm;

	private $em;

	private $connection;

	private $query;

	private $columns;

	private $distinct;

	private $target;

	private $page;

	private $offset;

	private $quantity;

	private $top;

	public function __construct(\PDO $connection, IEntityManager $em) {
		if (!$connection) {
			throw new \Exception('Conexão não definida');
		}

		$this->orm = Orm::getInstance();
		$this->em = $em;
		$this->connection = $connection;

		$this->columns = [];
		$this->joins = [];
		$this->joinsByAlias = [];
		$this->relations = [];
		$this->usedTables = [];
		$this->aggregations = [];
		$this->whereConditions = [];
		$this->groups = [];
		$this->havingConditions = [];
		$this->values = [];
		$this->orders = [];
	}

	public function distinct() {
		$this->distinct = true;

		return $this;
	}

	public function from(String $from, String $alias) {
		$shadow = $this->orm->getShadow($from);
		$shadow->setAlias($alias);

		$this->target = $shadow;
		$this->joinsByAlias[$alias] = $shadow;

		return $this;
	}

	public function page($page, $quantity) {
		if ($page <= 0) {
			throw new \Exception('The "page" argument must be an integer, positive and bigger than zero number');
		}

		if ($quantity <= 0) {
			throw new \Exception('The "quantity" argument must be an integer, positive and bigger than zero number');
		}

		$this->page = $page;
		$this->offset = ($page - 1) * $quantity;
		$this->quantity = $quantity;

		return $this;
	}

	public function top($top) {
		$this->top = $top;

		return $this;
	}

	public function all() {
		$this->generateQuery();

		$statement = $this->connection->prepare($this->query);
		$hasResults = $statement->execute($this->values);
		$resultSet = [];

		if ($hasResults) {
			$resultSet = $statement->fetchAll(\PDO::FETCH_ASSOC);

			if (empty($this->columns)) {
				$resultSet = $this->mapResultSet($resultSet);
			}
		}

		return $resultSet;
	}

	public function one() {
		$this->generateQuery();

		$statement = $this->connection->prepare($this->query);
		$executed = $statement->execute($this->values);
		$resultSet = null;

		if ($executed) {
			$resultSet = $statement->fetch(\PDO::FETCH_ASSOC);

			if (empty($this->columns) && $resultSet) {
				$resultSet = $this->mapOne($resultSet);
			}

			if (empty($resultSet)) {
				$resultSet = null;
			}
		}

		return $resultSet;
	}

	private function generateQuery() {
		$this->query = 'SELECT ';

		if ($this->distinct) {
			$this->query .= 'DISTINCT ';
		}

		$groupBy = $this->resolveGroupBy();
		$aggregations = $this->resolveAggregations();

		if (empty($this->columns)) {
			$this->query .= $this->target->getAlias() . '.*';
		} else {
			$this->query .= join(', ', $this->columns);
		}

		$this->query .= "\n" . 'FROM ' . $this->target->getTableName() . ' ' . $this->target->getAlias();

		if (property_exists(__CLASS__, 'usedTables')) {
			$this->usedTables[$this->target->getClass()] = $this->target;
		}

		if (count($this->joins)) {
			$this->preProcessJoins([$this->target], $this->joins);
			$this->query .= $this->generateJoins(null, $this->relations);
		}

		$this->query .= $this->resolveWhere();
		$this->query .= $this->resolveGroupBy();
		$this->query .= $this->resolveHaving();
		$this->query .= $this->resolveOrderBy();

		if (is_numeric($this->offset) && is_numeric($this->quantity)) {
			$this->query = sprintf(Driver::$PAGE_TEMPLATE, $this->query, $this->offset, $this->quantity);
		}

		if ($this->top) {
			$this->query = sprintf(Driver::$TOP_TEMPLATE, $this->query, $this->top);
		}
	}

	private function mapResultSet($resultSet) {
		$mapped = [];

		foreach ($resultSet as $result) {
			$proxy = $this->mapOne($result);
			array_push($mapped, $proxy);
		}

		return $mapped;
	}

	private function mapOne($resultSet) {
		$class = $this->target->getClass();
		$object = new $class;
		$values = [];

		foreach ($this->target->getColumns() as $column) {
			$object->{$column->getProperty()} = $this->convertType($resultSet[$column->getName()], $column->getType());
		}

		foreach ($this->target->getJoins() as $column) {
			if (isset($resultSet[$column->getName()])) {
				$values[$column->getProperty()] = $this->convertType($resultSet[$column->getName()], $column->getType());
			}
		}

		$proxy = new Proxy($this->em, $object, $this->target, $values);

		return $proxy;
	}

	public function convertType($value, $type) {
		switch ($type) {
			case 'int': return (int) $value;
			case 'float': return (float) $value;
			case 'datetime': return new \DateTime($value);
			default: return $value;
		}
	}

}
