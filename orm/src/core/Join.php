<?php

namespace ORM\Core;

class Join {

	private $shadow;

	private $reference;

	private $name;

	private $property;

	private $mappedBy;

	private $joinTable;

	private $type;

	private $cascade;

	private $optional;

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

	public function getName() {
		return $this->name;
	}

	public function setName($name) {
		$this->name = $name;
	}

	public function getProperty() {
		return $this->property;
	}

	public function setProperty($property) {
		$this->property = $property;
	}

	public function getMappedBy() {
		return $this->mappedBy;
	}

	public function setMappedBy($mappedBy) {
		$this->mappedBy = $mappedBy;
	}


	public function getJoinTable() {
		return $this->joinTable;
	}

	public function setJoinTable($joinTable) {
		$this->joinTable = $joinTable;
	}

	public function getType() {
		return $this->type;
	}

	public function setType($type) {
		$this->type = $type;
	}

	public function getCascade() {
		return $this->cascade;
	}

	public function setCascade($cascade) {
		$this->cascade = $cascade;
	}

	public function isOptional() {
		return $this->optional;
	}

	public function setOptional($optional) {
		$this->optional = $optional;
	}

}
