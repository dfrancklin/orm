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
	private $id;

	/**
	 * @ORM/Column(type=string, length=100)
	 */
	private $texto;

	/**
	 * @ORM/Column(type=datetime)
	 */
	private $data;

	/**
	 * @ORM/BelongsTo(class=App\Models\GreeningU\Usuario)
	 */
	private $usuario;

	/**
	 * @ORM/BelongsTo(class=App\Models\GreeningU\Post)
	 * @ORM/JoinColumn(name=id_postagem)
	 */
	private $post;

	public function getId() {
		return $this->id;
	}

	public function setId($id) {
		$this->id = $id;
	}

	public function getTexto() {
		return $this->texto;
	}

	public function setTexto($texto) {
		$this->texto = $texto;
	}

	public function getData() {
		return $this->data;
	}

	public function setData($data) {
		$this->data = $data;
	}

	public function getUsuario() {
		return $this->usuario;
	}

	public function setUsuario($usuario) {
		$this->usuario = $usuario;
	}

	public function getPost() {
		return $this->post;
	}

	public function setPost($post) {
		$this->post = $post;
	}

}
