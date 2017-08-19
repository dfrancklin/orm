<?php
namespace ORM\Builders;

use ORM\Core\Shadow;
use ORM\Core\Column;

class Criteria {

	const
		EQUALS = 'equals',
		NOT_EQUALS = 'notEquals',
		LIKE = 'like',
		NOT_LIKE = 'notLike',
		IS_NULL = 'isNull',
		IS_NOT_NULL = 'isNotNull',
		BETWEEN = 'between';

	private static $templates = [
		self::EQUALS => '%s = %s',
		self::NOT_EQUALS => '%s != %s',
		self::LIKE => '%s like %s',
		self::NOT_LIKE => '%s not like %s',
		self::IS_NULL => '%s is null',
		self::IS_NOT_NULL => '%s is not null',
		self::BETWEEN => '(%s between %s and %s)',
	];

	private $builder;

	private $action;

	private $values;

	public function __construct($builder) {
		$this->builder = $builder;
		$this->values = [];
	}

	public function equals($value) {
		$this->action = self::EQUALS;
		array_push($this->values, $value);

		return $this->builder;
	}

	public function notEquals($value) {
		$this->action = self::NOT_EQUALS;
		array_push($this->values, $value);

		return $this->builder;
	}

	public function like($value) {
		$this->action = self::LIKE;
		array_push($this->values, $value);

		return $this->builder;
	}

	public function notLike($value) {
		$this->action = self::NOT_LIKE;
		array_push($this->values, $value);

		return $this->builder;
	}

	public function isNull() {
		$this->action = self::IS_NULL;

		return $this->builder;
	}

	public function isNotNull() {
		$this->action = self::IS_NOT_NULL;

		return $this->builder;
	}

	public function between($value1, $value2) {
		$this->action = self::BETWEEN;
		array_push($this->values, $value1, $value2);

		return $this->builder;
	}

	// Alias

	public function eq($value) {
		return $this->equals($value);
	}

	public function neq($value) {
		return $this->notEquals($value);
	}

	public function l($value) {
		return $this->like($value);
	}

	public function nl($value) {
		return $this->notLike($value);
	}

	public function bt($value) {
		return $this->between($value);
	}

	// End Alias

	public function getAction() {
		return $this->action;
	}

	public function getTemplate() {
		if (!array_key_exists($this->action, self::$templates)) {
			throw new \Exception('Action "' . $this->action . '" does not exists.');
		}

		return self::$templates[$this->action];
	}

	public function getValues() {
		return $this->values;
	}

}
