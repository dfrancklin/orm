<?php

namespace App\Models\GreeningU;

/**
 * @ORM/Entity
 */
class Usuario {

	/**
	 * @ORM/Id
	 * @ORM/Generated
	 * @ORM/Column(type=int)
	 */
	public $id;

	/**
	 * @ORM/Column(type=string, length=20)
	 */
	public $nome;

	/**
	 * @ORM/Column(type=string, length=30)
	 */
	public $sobrenome;

	/**
	 * @ORM/Column(type=string, length=30)
	 */
	public $email;

	/**
	 * @ORM/Column(type=string, length=10)
	 */
	public $login;

	/**
	 * @ORM/Column(type=string, length=12)
	 */
	public $senha;

	/**
	 * @ORM/Column(type=string, length=1)
	 */
	public $sexo;

	/**
	 * @ORM/Column(type=int)
	 */
	public $pontuacao;

	/**
	 * @ORM/ManyToMany(class=App\Models\GreeningU\Comunidade)
	 * @ORM/JoinTable(tableName=usuario_comunidade, join={name=usuarios_id}, inverse={name=assinaturas_id})
	 */
	public $assinaturas;

	/**
	 * @ORM/HasMany(class=App\Models\GreeningU\Comunidade)
	 */
	public $comunidades;

	/**
	 * @ORM/HasMany(class=App\Models\GreeningU\Post)
	 */
	public $posts;

	/**
	 * @ORM/HasMany(class=App\Models\GreeningU\Comentario)
	 */
	public $comentarios;

	/**
	 * @ORM/HasMany(class=App\Models\GreeningU\Voto)
	 */
	public $votos;

}
