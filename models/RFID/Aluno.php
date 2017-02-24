<?php
namespace App\Models\RFID;

/**
 * @ORM/Entity
 * @ORM/Table(name=alunos)
 */
class Aluno {

	/**
	 * @ORM/Id
	 * @ORM/Column(unique=true)
	 */
	private $matricula;

	/**
	 * @ORM/Column(name=nome_aluno)
	 */
	private $nome;

	/**
	 * @ORM/Column(name=tag_aluno)
	 */
	private $tag;

	/**
	 * @ORM/ManyToMany(class=App\Models\RFID\Responsavel, mappedBy=alunos)
	 */
	private $responsaveis;

	public function getMatricula() {
		return $this->matricula;
	}

	public function setMatricula($matricula) {
		$this->matricula = $matricula;
	}

	public function getNome() {
		return $this->nome;
	}

	public function setNome($nome) {
		$this->nome = $nome;
	}

	public function getTag() {
		return $this->tag;
	}

	public function setTag($tag) {
		$this->tag = $tag;
	}

	public function getResponsaveis() {
		return $this->responsaveis;
	}

	public function setResponsaveis($responsaveis) {
		$this->responsaveis = $responsaveis;
	}

}
