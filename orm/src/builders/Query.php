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
			$this->query .= $this->generateJoins($this->from);
	}

	private function generateJoins($shadow) {
		if (!$shadow) return;
		
		$joins = array_merge($shadow->getHasOne(), $shadow->getHasMany(), $shadow->getBelongsTo());
		
		foreach($joins as $join) {
			$reference = $join->getReference();

			if (array_key_exists($reference, $this->joins) && !array_key_exists($reference, $this->usedTables)) {
				$this->query .= $this->resolveJoin($join, $this->joins[$reference]);
				$this->usedTables[$reference] = $this->joins[$reference];
			}
		}
		
		$next = array_shift($this->joins);
		
		return $this->generateJoins($next);
	}

	private function resolveJoin($join, $shadow) {
		$method = 'resolveJoin' . ucfirst($join->getType());

		return $this->$method($join, $shadow);
	}

	private function resolveJoinHasOne($join, $shadow) {
		$sql = ' INNER JOIN ';
		$sql .= $shadow->getTableName();
		$sql .= ' ON ';
		$sql .= $join->getShadow()->getTableName() . '.';
		
		$belongsTo = $shadow->getBelongsTo($join->getShadow()->getClass());
		
		if (!is_array($belongsTo) && $belongsTo) {
			$sql .= $belongsTo->getName() . ' = ';
		} else {
			$sql .= $shadow->getTableName() . '_';
			$sql .= $shadow->getId()->getName() . ' = ';
		}

		$sql .= $shadow->getTableName() . '.';
		$sql .= $shadow->getId()->getName();

		return $sql;
	}
	
	private function resolveJoinHasMany($join, $shadow) {
		$sql = ' INNER JOIN ';
		$sql .= $shadow->getTableName();
		$sql .= ' ON ';
		$sql .= $join->getShadow()->getTableName() . '.';
		$sql .= $join->getShadow()->getId()->getName() . ' = ';
		$sql .= $shadow->getTableName() . '.';

		$belongsTo = $shadow->getBelongsTo($join->getShadow()->getClass());
		
		if (!is_array($belongsTo) && $belongsTo) {
			$sql .= $belongsTo->getName();
		} else {
			$sql .= $join->getShadow()->getTableName() . '_';
			$sql .= $join->getShadow()->getId()->getName();
		}
		
		return $sql;
	}

	private function resolveJoinBelongsTo($join, $shadow) {
		$sql = ' INNER JOIN ';
		$sql .= $shadow->getTableName();
		$sql .= ' ON ';
		$sql .= $shadow->getTableName() . '.';
		$sql .= $shadow->getId()->getName() . ' = ';
		$sql .= $join->getShadow()->getTableName() . '.';
		$sql .= $join->getName();

		return $sql;
	}

}