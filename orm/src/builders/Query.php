<?php
namespace ORM\Builders;

use ORM\Orm;

class Query extends Orm {

	use Where;

	private $connection;

	private $query;

	private $distinct;

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

	public function distinct(bool $distinct) {
		$this->distinct = $distinct;

		return $this;
	}

	public function from($from) {
		$this->from = self::getShadow($from);

		$this->usedTables[$this->from->getClass()] = $this->from;

		return $this;
	}

	public function joins($joins) {
		$this->joins = [];

		foreach($joins as $join) {
			if (is_array($join)) {
				$this->joins[$join[0]][0] = self::getShadow($join[0]);
				$this->joins[$join[0]][1] = $join[1];
			} else {
				$this->joins[$join] = self::getShadow($join);
			}
		}

		return $this;
	}

	public function all() {
		$this->generateQuery();

		vd($this->query);

		$query = $this->connection->query($this->query);

		if ($query) {
			return $query->fetchAll(\PDO::FETCH_ASSOC);
		}
	}

	private function generateQuery() {
		$this->query = 'SELECT ';
		
		if ($this->distinct) {
			$this->query .= 'DISTINCT ';
		}
		
		$this->query .= $this->from->getTableName() . '.* FROM ' . $this->from->getTableName();

		if (count($this->joins)) {
			$this->query .= $this->generateJoins($this->from);
		}
	}

	private function generateJoins($shadow) {
		if (!$shadow) return;
		
		$joins = array_merge($shadow->getHasOne(), $shadow->getHasMany(), $shadow->getManyToMany(), $shadow->getBelongsTo());
		
		foreach($joins as $join) {
			$reference = $join->getReference();

			if (array_key_exists($reference, $this->joins)) {
				$this->query .= $this->resolveJoin($join, $this->joins[$reference]);
				$this->usedTables[$reference] = $this->joins[$reference];
			} elseif ($this->from->getClass() === $reference) {
				$this->query .= $this->resolveJoin($join,  $this->from);
			}
		}
		
		$next = array_shift($this->joins);
		
		return $this->generateJoins($next);
	}

	private function resolveJoin($join, $shadow) {
		if (array_key_exists($join->getShadow()->getClass(), $this->usedTables) &&
			array_key_exists($shadow->getClass(), $this->usedTables)) {
			return;
		}
		
		$method = 'resolveJoin' . ucfirst($join->getType());

		return $this->$method($join, $shadow);
	}

	private function resolveJoinHasOne($join, $shadow) {
		$sql = "\n\t" . ' INNER JOIN ';
		$sql .= $shadow->getTableName();
		$sql .= ' ON ';
		$sql .= $join->getShadow()->getTableName() . '.';
		
		$belongsTo = $shadow->getBelongsTo('reference', $join->getShadow()->getClass());
		
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
		if (array_key_exists($join->getShadow()->getClass(), $this->usedTables) &&
			array_key_exists($shadow->getClass(), $this->usedTables)) {
			return;
		}

		$sql = "\n\t" . ' INNER JOIN ';
		$sql .= $shadow->getTableName();
		$sql .= ' ON ';
		$sql .= $join->getShadow()->getTableName() . '.';
		$sql .= $join->getShadow()->getId()->getName() . ' = ';
		$sql .= $shadow->getTableName() . '.';

		$belongsTo = $shadow->getBelongsTo('reference', $join->getShadow()->getClass());
		
		if (!is_array($belongsTo) && $belongsTo) {
			$sql .= $belongsTo->getName();
		} else {
			$sql .= $join->getShadow()->getTableName() . '_';
			$sql .= $join->getShadow()->getId()->getName();
		}
		
		return $sql;
	}

	public function resolveJoinManyToMany($join, $shadow) {
		if ($join->getMappedBy()) {
			$tempJoin = $shadow->getManyToMany('property', $join->getMappedBy());
			$shadow = $join->getShadow();
			$join = $tempJoin;
		}

		$sql = "\n\t" . ' INNER JOIN ';
		
		if (array_key_exists($shadow->getClass(), $this->usedTables)) {
			$sql .= $join->getJoinTable()->getTableName();
			$sql .= ' ON ';
			$sql .= $shadow->getTableName() . '.';
			$sql .= $shadow->getId()->getName() . ' = ';
			$sql .= $join->getJoinTable()->getTableName() . '.';
			$sql .= $join->getJoinTable()->getInverseJoinColumnName();
		}


		$sql .= "\n\t" . ' INNER JOIN ';
		$sql .= $join->getShadow()->getTableName();
		$sql .= ' ON ';
		$sql .= $join->getShadow()->getTableName() . '.';
		$sql .= $join->getShadow()->getId()->getName() . ' = ';
		$sql .= $join->getJoinTable()->getTableName() . '.';
		$sql .= $join->getJoinTable()->getJoinColumnName();

		return $sql;
	}

	private function resolveJoinBelongsTo($join, $shadow) {
		$sql = "\n\t" . ' INNER JOIN ';

		if (array_key_exists($shadow->getClass(), $this->usedTables)) {
			$sql .= $join->getShadow()->getTableName();	
		} else {
			$sql .= $shadow->getTableName();
		}

		$sql .= ' ON ';
		$sql .= $shadow->getTableName() . '.';
		$sql .= $shadow->getId()->getName() . ' = ';
		$sql .= $join->getShadow()->getTableName() . '.';
		$sql .= $join->getName();

		return $sql;
	}

}