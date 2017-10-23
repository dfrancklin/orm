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
		self::MIN => self::MIN . '(%s)',
		self::MAX => self::MAX . '(%s)',
		self::COUNT => self::COUNT . '(%s)',
	];

	private $builder;

	private $action;

	private $criteria;

	public function __construct($builder) {
		$this->builder = $builder;
		$this->criteria = [];
	}

	public function __call($method, $parameters) {
		if (!array_key_exists($method, self::$templates)) {
			throw new \Exception('Invalid method "' . $method . '" of the "' . __CLASS__ . '" class');
		}

		if (!count($parameters)) {
			throw new \Exception('The method "' . $method . '" expects 1 argument and ' . count($parameters) . ' was provided.');
		}

		$criteria = new Criteria($this->builder);

		$this->action = $method;
		$this->criteria = [$parameters[0], $criteria];

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
