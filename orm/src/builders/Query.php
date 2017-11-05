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

	public function distinct() : Query
	{
		$this->distinct = true;

		return $this;
	}

	public function from(String $from, String $alias=null) : Query
	{
		$shadow = $this->orm->getShadow($from);

		if (empty($alias)) {
			$alias = strtolower($shadow->getTableName()[0]);
		}

		$shadow->setAlias($alias);

		$this->target = $shadow;
		$this->joinsByAlias[$alias] = $shadow;

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

		vd($query, $this->values);

		$statement = $this->connection->prepare($query);
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

	public function one()
	{
		$this->top(1);
		$query = $this->generateQuery();

		vd($query, $this->values);
		$statement = $this->connection->prepare($query);
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

	private function generateQuery() : String
	{
		$query = 'SELECT ';

		if ($this->distinct) {
			$query .= 'DISTINCT ';
		}

		$groupBy = $this->resolveGroupBy();
		$aggregations = $this->resolveAggregations();

		if (empty($this->columns)) {
			$query .= $this->target->getAlias() . '.*';
		} else {
			$query .= join(', ', $this->columns);
		}

		$tableName = '';

		if (!empty($this->target->getSchema())) {
			$tableName .= $this->target->getSchema() . '.';
		} elseif (!empty($this->connection->getDefaultSchema())) {
			$tableName .= $this->connection->getDefaultSchema() . '.';
		}

		$tableName .= $this->target->getTableName();

		$query .= "\n" . 'FROM ' . $tableName . ' ' . $this->target->getAlias();

		if (property_exists(__CLASS__, 'usedTables')) {
			$this->usedTables[$this->target->getClass()] = $this->target;
		}

		$query .= $this->resolveJoin();
		$query .= $this->resolveWhere();
		$query .= $this->resolveGroupBy();
		$query .= $this->resolveHaving();
		$query .= $this->resolveOrderBy();

		if (is_numeric($this->offset) && is_numeric($this->quantity)) {
			$query = sprintf($this->connection->getDriver()->PAGE_TEMPLATE, $query, $this->offset, $this->quantity);
		}

		if ($this->top) {
			$query = sprintf($this->connection->getDriver()->TOP_TEMPLATE, $query, $this->top);
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
		$class = $this->target->getClass();
		$object = new $class;

		foreach ($this->target->getColumns() as $column) {
			$name = $column->getName();

			if (isset($resultSet[$name])) {
				$value = $resultSet[$name];
				$type = $column->getType();
				$property = $column->getProperty();

				$object->{$property} = $this->convertType($value, $type);
			}
		}

		$joins = $this->target->getJoins();

		if (empty($joins)) {
			return $object;
		}

		$values = [];

		foreach ($joins as $column) {
			$name = $column->getName();

			if (isset($resultSet[$name])) {
				$value = $resultSet[$name];
				$type = $column->getType();
				$property = $column->getProperty();

				$values[$property] = $this->convertType($value, $type);
			}
		}

		$proxy = new Proxy($this->em, $object, $values);

		return $proxy;
	}

	public function convertType($value, $type)
	{
		switch ($type) {
			case 'int':
				return (int) $value;
			case 'float':
				return (float) $value;
			case 'datetime':
				return new \DateTime($value);
			default:
				return $value;
		}
	}

}
