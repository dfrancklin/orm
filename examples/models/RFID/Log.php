<?php

namespace App\Models\RFID;

/**
 * @ORM/Entity
 */
class Log {

	/**
	 * @ORM/Id
	 * @ORM/Generated
	 * @ORM/Column(type=int)
	 */
	private $id;

	/**
	 * @ORM/Column(type=date)
	 */
	private $data;

	/**
	 * @ORM/Column(type=time)
	 */
	private $hora;

	/**
	 * @ORM/BelongsTo(class=App\Models\RFID\Ambiente)
	 * @ORM/JoinColumn(name=ambiente)
	 */
	private $ambiente;

	/**
	 * @ORM/BelongsTo(class=App\Models\RFID\Aluno)
	 * @ORM/JoinColumn(name=aluno)
	 */
	private $aluno;

	public function getId() {
		return $this->id;
	}

	public function setId($id) {
		$this->id = $id;
	}

	public function getData() {
		return $this->data;
	}

	public function setData($data) {
		$this->data = $data;
	}

	public function getHora() {
		return $this->hora;
	}

	public function setHora($hora) {
		$this->hora = $hora;
	}

	public function getAmbiente() {
		return $this->ambiente;
	}

	public function setAmbiente($ambiente) {
		$this->ambiente = $ambiente;
	}

	public function getAluno() {
		return $this->aluno;
	}

	public function setAluno($aluno) {
		$this->aluno = $aluno;
	}

}
