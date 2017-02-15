<?php
namespace App\Models\Store;

/**
 * @ORM/Entity
 * @ORM/Table(name=orders)
 */
class Order {

	private $id;

	/**
	 * @ORM/BelongsTo(class=App\Model\Client)
	 */
	private $client;

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