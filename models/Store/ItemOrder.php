<?php
namespace App\Models\Store;

/**
 * @ORM/Entity
 * @ORM/Table(name=item_order)
 */
class ItemOrder {

	/**
	 * @ORM/Id
	 * @ORM/Generated
	 * @ORM/Column(type=int)
	 */
	private $id;

	/**
	 * @ORM/BelongsTo(class=App\Models\Store\Order)
	 */
	private $order;

	/**
	 * @ORM/HasOne(class=App\Models\Store\Product)
	 */
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