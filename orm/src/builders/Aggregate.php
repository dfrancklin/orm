<?php
namespace ORM\Builders;

class Aggregate {

	const
		AVG = 'avg',
		SUM = 'sum',
		MIN = 'min',
		MAX = 'max',
		COUNT = 'count';

	private static $templates = [
		self::AVG => self::AVG . '(%s)',
		self::SUM => self::SUM . '(%s)',
		self::COUNT => self::COUNT . '(%s)',
	];

	private $builder;

	private $action;

	private $criteria;

	public function __construct($builder) {
		$this->builder = $builder;
		$this->criteria = [];
	}

	public function avg($property) {
		$criteria = new Criteria($this->builder);

		$this->action = self::AVG;
		$this->criteria = [$property, $criteria];

		return $criteria;
	}

	public function sum($property) {
		$criteria = new Criteria($this->builder);

		$this->action = self::SUM;
		$this->criteria = [$property, $criteria];

		return $criteria;
	}

	public function getAction() {
		return $this->action;
	}

	public function getCriteria() {
		return $this->criteria;
	}

	public function getTemplate() {
		if (!array_key_exists($this->action, self::$templates)) {
			throw new \Exception('Action "' . $this->action . '" does not exists.');
		}

		return self::$templates[$this->action];
	}

}
