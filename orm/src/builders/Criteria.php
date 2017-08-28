<?php
namespace ORM\Builders;

use ORM\Core\Shadow;
use ORM\Core\Column;

class Criteria {

	const
		EQUALS = 'equals',
		NOT_EQUALS = 'notEquals',
		IS_NULL = 'isNull',
		IS_NOT_NULL = 'isNotNull',
		BETWEEN = 'between',
		GREATER_THAN = 'greaterThan',
		GREATER_OR_EQUALS_THAN = 'greaterOrEqualsThan',
		LESS_THAN = 'lessThan',
		LESS_OR_EQUALS_THAN = 'lessOrEqualsThan',
		IN = 'in',
		NOT_IN = 'notIn',
		LIKE = 'like',
		NOT_LIKE = 'notLike';

	private static $templates = [
		self::EQUALS => '%s = %s',
		self::NOT_EQUALS => '%s != %s',
		self::IS_NULL => '%s is null',
		self::IS_NOT_NULL => '%s is not null',
		self::BETWEEN => '(%s between %s and %s)',
		self::GREATER_THAN => '%s > %s',
		self::GREATER_OR_EQUALS_THAN => '%s >= %s',
		self::LESS_THAN => '%s < %s',
		self::LESS_OR_EQUALS_THAN => '%s <= %s',
		self::IN => '%s in (%s)',
		self::NOT_IN => '%s not in (%s)',
		self::LIKE => '%s like %s',
		self::NOT_LIKE => '%s not like %s',
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

	public function greaterThan($value) {
		$this->action = self::GREATER_THAN;
		array_push($this->values, $value);

		return $this->builder;
	}

	public function greaterOrEqualsThan($value) {
		$this->action = self::GREATER_OR_EQUALS_THAN;
		array_push($this->values, $value);

		return $this->builder;
	}

	public function lessThan($value) {
		$this->action = self::LESS_THAN;
		array_push($this->values, $value);

		return $this->builder;
	}

	public function lessOrEqualsThan($value) {
		$this->action = self::LESS_OR_EQUALS_THAN;
		array_push($this->values, $value);

		return $this->builder;
	}

	public function in(...$values) {
		$this->action = self::IN;
		array_push($this->values, $values);

		return $this->builder;
	}

	public function notIn(...$values) {
		$this->action = self::NOT_IN;
		array_push($this->values, $values);

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

	public function beginsWith($value) {
		return $this->like($value . '%');
	}

	public function notBeginsWith($value) {
		return $this->notLike($value . '%');
	}

	public function endsWith($value) {
		return $this->like('%' . $value);
	}

	public function notEndsWith($value) {
		return $this->notLike('%' . $value);
	}

	public function contains($value) {
		return $this->like('%' . $value . '%');
	}

	public function notContains($value) {
		return $this->notLike('%' . $value . '%');
	}

	// Alias

	public function eq($value) {
		return $this->equals($value);
	}

	public function neq($value) {
		return $this->notEquals($value);
	}

	public function bt($value) {
		return $this->between($value);
	}

	public function gt($value) {
		$this->action = self::GREATER_THAN;
		array_push($this->values, $value);

		return $this->builder;
	}

	public function goet($value) {
		$this->action = self::GREATER_OR_EQUALS_THAN;
		array_push($this->values, $value);

		return $this->builder;
	}

	public function lt($value) {
		$this->action = self::LESS_THAN;
		array_push($this->values, $value);

		return $this->builder;
	}

	public function loet($value) {
		$this->action = self::LESS_OR_EQUALS_THAN;
		array_push($this->values, $value);

		return $this->builder;
	}

	public function l($value) {
		return $this->like($value);
	}

	public function nl($value) {
		return $this->notLike($value);
	}

	public function bw($value) {
		return $this->beginsWith($value);
	}

	public function nbw($value) {
		return $this->notBeginsWith($value);
	}

	public function ew($value) {
		return $this->endsWith($value);
	}

	public function newt($value) {
		return $this->notEndsWith($value);
	}

	public function ctn($value) {
		return $this->contains($value);
	}

	public function nctn($value) {
		return $this->notContains($value);
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
