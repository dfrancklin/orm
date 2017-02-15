<?php
namespace App\Models\Store;

/**
 * @ORM/Entity
 * @ORM/Table(name=products)
 */
class Product {

	private $id;

	private $description;

	private $value;

	public function getId() {
		return $this->id;
	}

	public function setId($id) {
		$this->id = $id;
	}

	public function getDescription() {
		return $this->description;
	}

	public function setDescription($description) {
		$this->description = $description;
	}

	public function getValue() {
		return $this->value;
	}

	public function setValue($value) {
		$this->value = $value;
	}

}