<?php

namespace App\Models\GreeningU;

/**
 * @ORM/Entity
 */
class Comentario {

	/**
	 * @ORM/Id
	 * @ORM/Generated
	 * @ORM/Column(type=int)
	 */
	public $id;

	/**
	 * @ORM/Column(type=string, length=100)
	 */
	public $texto;

	/**
	 * @ORM/Column(type=datetime)
	 */
	public $data;

	/**
	 * @ORM/BelongsTo(class=App\Models\GreeningU\Usuario)
	 */
	public $usuario;

	/**
	 * @ORM/BelongsTo(class=App\Models\GreeningU\Post)
	 * @ORM/JoinColumn(name=id_postagem)
	 */
	public $post;

}
