<?php

namespace App\Models\GreeningU;

/**
 * @ORM/Entity
 */
class Comunidade {

	/**
	 * @ORM/Id
	 * @ORM/Generated
	 * @ORM/Column(type=int)
	 */
	public $id;

	/**
	 * @ORM/Column(name=name, type=string, length=45)
	 */
	public $nome;

	/**
	 * @ORM/Column(type=datetime)
	 */
	public $data;

	/**
	 * @ORM/ManyToMany(class=App\Models\GreeningU\Usuario, mappedBy=assinaturas)
	 */
	public $usuarios;

	/**
	 * @ORM/BelongsTo(class=App\Models\GreeningU\Usuario)
	 */
	public $lider;

	/**
	 * @ORM/HasMany(class=App\Models\GreeningU\Post)
	 */
	public $posts;

}
