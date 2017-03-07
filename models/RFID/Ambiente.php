<?php
namespace App\Models\RFID;

/**
 * @ORM/Entity
 * @ORM/Table(name=ambientes)
 */
class Ambiente {

	/**
	 * @ORM/Id
	 * @ORM/Generated
	 * @ORM/Column(type=int)
	 */
	private $id;

	/**
	 * @ORM/Column(name=id_leitor)
	 */
	private $leitor;

	/**
	 * @ORM/Column(name=desc_ambiente)
	 */
	private $descricao;

	public function getId() {
		return $this->id;
	}

	public function setId($id) {
		$this->id = $id;
	}

	public function getLeitor() {
		return $this->leitor;
	}

	public function setLeitor($leitor) {
		$this->leitor = $leitor;
	}

	public function getDescricao() {
		return $this->descricao;
	}

	public function setDescricao($descricao) {
		$this->descricao = $descricao;
	}

}
