<?php
namespace ORM\Builders;

use ORM\Orm;

class Query extends Orm {

	use Where;

	private $connection;

	private $query;

	private $from;

	private $joins;

	private $usedTables;

	public function __construct($connection, $from = '', $joins = []) {
		if (!$connection)
			throw new \Exception('Conexão não definida', 1);

		$this->connection = $connection;

		if ($from)
			$this->from($from);

		if (count($joins))
			$this->joins($joins);

		$this->usedTables = [];
	}

	public function from($from) {
		$this->from = self::getShadow($from);

		$this->usedTables[$this->from->getTableName()] = $from;

		return $this;
	}

	public function joins($joins) {
		$this->joins = [];

		foreach($joins as $join)
			$this->joins[$join] = self::getShadow($join);

		return $this;
	}

	public function all() {
		$this->generateQuery();

		echo $this->query;

		$query = $this->connection->query($this->query);

		if ($query) {
			return $query->fetchAll(\PDO::FETCH_ASSOC);
		}
	}

	private function generateQuery() {
		$this->query = 'SELECT ' . $this->from->getTableName() . '.* FROM ' . $this->from->getTableName();

		if (count($this->joins))
			$this->query .= $this->generateJoins();
	}

	private function generateJoins($join = null) {
		if (!$join) {
			if (count($this->joins)) {
				return $this->generateJoins(array_shift($this->joins));
			} else {
				return;
			}
		}
		
		$sql = ' INNER JOIN ';

		if (array_key_exists($join->getTableName(), $this->usedTables)) {
			echo 'do something<br>';
		} else {
			$this->usedTables[$join->getTableName()] = $join;
			
			echo 'do something else<br>';

			return $this->generateJoins(array_shift($this->joins));
		}

		return $sql;
	}

}