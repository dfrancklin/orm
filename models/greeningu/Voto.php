<?php

namespace App\Models\GreeningU;

/**
 * @ORM/Entity
 */
class Voto {

	/**
	 * @ORM/Id
	 * @ORM/Generated
	 * @ORM/Column(type=int)
	 */
	public $id;

	/**
	 * @ORM/BelongsTo(class=App\Models\GreeningU\Usuario)
	 * @ORM/JoinColumn(name=id_usuario_votador)
	 */
	public $usuario;

	/**
	 * @ORM/BelongsTo(class=App\Models\GreeningU\Post)
	 * @ORM/JoinColumn(name=id_postagem)
	 */
	public $post;

	/**
	 * @ORM/Column(name=data_voto, type=datetime)
	 */
	public $data;

	/**
	 * @ORM/Column(type=int)
	 */
	public $pontos;

}
