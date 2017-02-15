<?php
namespace App\Models\Store;

/**
 * @ORM/Entity
 * @ORM/Table(name=items_order)
 */
class ItemOrder {

	private $order;

	private $product;

	private $quantity;

	public function __get($property) {
		if (property_exists(__CLASS__, $property)) {
			return $this->$property;
		}
	}

	public function __set($property, $value) {
		if (property_exists(__CLASS__, $property)) {
			$this->$property = $value;
		}
	}

}