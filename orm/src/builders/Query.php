<?php

namespace ORM\Builders;

use ORM\Orm;

use ORM\Core\Proxy;

use ORM\Builders\Handlers\AggregateHandler;
use ORM\Builders\Handlers\GroupByHandler;
use ORM\Builders\Handlers\HavingHandler;
use ORM\Builders\Handlers\JoinHandler;
use ORM\Builders\Handlers\OperatorHandler;
use ORM\Builders\Handlers\OrderByHandler;
use ORM\Builders\Handlers\WhereHandler;

use ORM\Interfaces\IConnection;
use ORM\Interfaces\IEntityManager;

class Query
{

	use AggregateHandler,
		GroupByHandler,
		HavingHandler,
		JoinHandler,
		OperatorHandler,
		OrderByHandler,
		WhereHandler;

	private $orm;

	private $em;

	private $connection;

	private $columns;

	private $distinct;

	private $target;

	private $page;

	private $offset;

	private $quantity;

	private $top;

	public function __construct(IConnection $connection, IEntityManager $em)
	{
		if (!$connection) {
			throw new \Exception('Conexão não definida');
		}

		$this->orm = Orm::getInstance();
		$this->em = $em;
		$this->connection = $connection;
		$this->logger = $this->orm->getLogger();

		$this->selected = [];
		$this->columns = [];
		$this->joins = [];
		$this->tablesByAlias = [];
		$this->relations = [];
		$this->usedTables = [];
		$this->aggregations = [];
		$this->whereConditions = [];
		$this->groups = [];
		$this->havingConditions = [];
		$this->values = [];
		$this->orders = [];
	}

	public function select(...$properties) : Query
	{
		array_push($this->selected, ...$properties);

		return $this;
	}

	public function distinct() : Query
	{
		$this->distinct = true;

		return $this;
	}

	public function from(String $from, String $alias=null) : Query
	{
		$table = $this->orm->getTable($from);

		if (empty($alias)) {
			$alias = strtolower($table->getName()[0]);
		}

		$this->target = [$table, $alias];
		$this->tablesByAlias[$alias] = [$table, $alias];

		return $this;
	}

	public function page($page, $quantity) : Query
	{
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

	public function top($top) : Query
	{
		$this->top = $top;

		return $this;
	}

	public function list() : Array
	{
		$query = $this->generateQuery();
		$statement = $this->connection->prepare($query);
		$executed = $statement->execute($this->values);

		if (isset($this->logger)) {
			$log = $query;

			if (!empty($this->values)) {
				$log .= "\n" . print_r($this->values, true);
			}

			$this->logger->debug($log, static::class);
		}

		$resultSet = [];

		if ($executed) {
			$resultSet = $statement->fetchAll(\PDO::FETCH_ASSOC);

			if (empty($this->columns)) {
				$resultSet = $this->mapResultSet($resultSet);
			}
		}

		return $resultSet;
	}

	public function one()
	{
		$this->top(1);
		$query = $this->generateQuery();
		$statement = $this->connection->prepare($query);
		$executed = $statement->execute($this->values);

		if (isset($this->logger)) {
			$log = $query;

			if (!empty($this->values)) {
				$log .= "\n" . print_r($this->values, true);
			}

			$this->logger->debug($log, static::class);
		}

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

	private function generateQuery() : String
	{
		$query = 'SELECT ';

		if ($this->distinct) {
			$query .= 'DISTINCT ';
		}

		$aggregations = $this->resolveAggregations();
		$groupBy = $this->resolveGroupBy();

		foreach ($this->selected as $column) {
			list($column) = $this->processProperty($column);

			$this->columns[] = $column;
		}

		list($table, $alias) = $this->target;

		if (empty($this->columns)) {
			$query .= $alias . '.*';
		} else {
			$query .= join(', ', $this->columns);
		}

		$tableName = '';

		if (!empty($table->getSchema())) {
			$tableName .= $table->getSchema() . '.';
		} elseif (!empty($this->connection->getDefaultSchema())) {
			$tableName .= $this->connection->getDefaultSchema() . '.';
		}

		$tableName .= $table->getName();

		$query .= "\n" . 'FROM ' . $tableName . ' ' . $alias;

		if (property_exists(__CLASS__, 'usedTables')) {
			$this->usedTables[$alias . ':' . $table->getClass()] = $table;
		}

		$query .= $this->resolveJoin();
		$query .= $this->resolveWhere();
		$query .= $this->resolveGroupBy();
		$query .= $this->resolveHaving();
		$query .= $this->resolveOrderBy();

		if (is_numeric($this->offset) && is_numeric($this->quantity)) {
			$driver = $this->connection->getDriver();
			$query = sprintf($driver->PAGE_TEMPLATE, $query, $this->offset, $this->quantity);
		}

		if ($this->top) {
			$driver = $this->connection->getDriver();
			$query = sprintf($driver->TOP_TEMPLATE, $query, $this->top);
		}

		return $query;
	}

	private function mapResultSet($resultSet) : Array
	{
		$mapped = [];

		foreach ($resultSet as $result) {
			$proxy = $this->mapOne($result);
			$mapped[] = $proxy;
		}

		return $mapped;
	}

	private function mapOne($resultSet)
	{
		list($table) = $this->target;
		$class = $table->getClass();
		$object = new $class;
		$driver = $this->connection->getDriver();

		foreach ($table->getColumns() as $column) {
			$name = $column->getName();

			if (in_array($name, array_keys($resultSet))) {
				$value = $resultSet[$name];
				$type = $column->getType();
				$property = $column->getProperty();

				$object->{$property} = $driver->convertToType($value, $type);
			}
		}

		$joins = $table->getJoins();

		if (empty($joins)) {
			return $object;
		}

		$values = [];

		foreach ($joins as $column) {
			$name = $column->getName();

			if (in_array($name, array_keys($resultSet))) {
				$value = $resultSet[$name];
				$refTable = $this->orm->getTable($column->getReference());
				$id = $refTable->getId();
				$type = $id->getType();
				$property = $column->getProperty();

				$values[$property] = $driver->convertToType($value, $type);
			}
		}

		$proxy = new Proxy($this->em, $object, $values);

		return $proxy;
	}

}
