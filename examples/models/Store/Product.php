<?php

namespace App\Models\Store;

/**
 * @ORM/Entity
 * @ORM/Table(name=products)
 */
class Product
{

	/**
	 * @ORM/Id
	 * @ORM/Generated
	 * @ORM/Column(type=int)
	 */
	private $id;

	private $description;

	/**
	 * @ORM/Column(type=float)
	 */
	private $value;

}
