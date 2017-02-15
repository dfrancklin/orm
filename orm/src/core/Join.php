<?php
namespace ORM\Core;

class Join {

	private $shadow;

	private $reference;

	private $property;

	private $type;

	public function getShadow() {
		return $this->shadow;
	}

	public function setShadow($shadow) {
		$this->shadow = $shadow;
	}

	public function getClass() {
		return $this->class;
	}

	public function setClass($class) {
		$this->class = $class;
	}

	public function getReference() {
		return $this->reference;
	}

	public function setReference($reference) {
		$this->reference = $reference;
	}

	public function getProperty() {
		return $this->property;
	}

	public function setProperty($property) {
		$this->property = $property;
	}

	public function getType() {
		return $this->type;
	}

	public function setType($type) {
		$this->type = $type;
	}

}