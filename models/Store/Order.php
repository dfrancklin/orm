<?php
namespace App\Models\Store;

/**
 * @ORM/Entity
 * @ORM/Table(name=orders)
 */
class Order {

	/**
	 * @ORM/Id
	 * @ORM/Generated
	 * @ORM/Column(type=int)
	 */
	private $id;

	/**
	 * @ORM/BelongsTo(class=App\Models\Store\Client)
	 */
	private $client;

	/**
	 * @ORM/HasMany(class=App\Models\Store\ItemOrder)
	 */
	private $items;

	public function getId() {
		return $this->id;
	}

	public function setId($id) {
		$this->id = $id;
	}

	public function getClient() {
		return $this->client;
	}

	public function setClient($client) {
		$this->client = $client;
	}

	public function getItems() {
		return $this->items;
	}

	public function setItems($items) {
		$this->items = $items;
	}

}