<?php
namespace ORM\Core;

class Column {

	private $shadow;

	private $id;

	private $generated;

	private $name;

	private $type;

	private $length;

	private $nullable;

	private $unique;

	public function getShadow() {
		return $this->shadow;
	}

	public function setShadow($shadow) {
		$this->shadow = $shadow;
	}

	public function getId() {
		return $this->id;
	}

	public function setId($id) {
		$this->id = $id;
	}

	public function isGenerated() {
		return $this->generated;
	}

	public function setGenerated($generated) {
		$this->generated = $generated;
	}

	public function getName() {
		return $this->name;
	}

	public function setName($name) {
		$this->name = $name;
	}

	public function getType() {
		return $this->type;
	}

	public function setType($type) {
		$this->type = $type;
	}

	public function getLength() {
		return $this->length;
	}

	public function setLength($length) {
		$this->length = $length;
	}

	public function getNullable() {
		return $this->nullable;
	}

	public function setNullable($nullable) {
		$this->nullable = $nullable;
	}

	public function isUnique() {
		return $this->unique;
	}

	public function setUnique($unique) {
		$this->unique = $unique;
	}

}