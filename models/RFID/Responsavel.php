<?php
namespace App\Models\RFID;

/**
 * @ORM/Entity
 * @ORM/Table(name=responsaveis)
 */
class Responsavel {

	/**
	 * @ORM/Id
	 * @ORM/Generated
	 * @ORM/Column(type=integer, name=id_resp)
	 */
	private $id;

	/**
	 * @ORM/Column(name=nome_resp)
	 */
	private $nome;

	/**
	 * @ORM/Column(name=cel_resp)
	 */
	private $celular;

	/**
	 * @ORM/Column(name=email_resp, unique=true)
	 */
	private $email;

	private $senha;

	private $nivel;

	/**
	 * @ORM\ManyToMany(class=App\Models\RFID\Aluno)
	 * @ORM\JoinTable(tableName=responsavel_aluno,
	 *					joinColumns={@JoinColumn(name=resp_id, referencedColumnName=id_resp)},
	 *					inverseJoinColumns={@JoinColumn(name=aluno_id, referencedColumnName=matricula)})
	 */
	private $alunos;

	public function getId() {
		return $this->id;
	}

	public function setId($id) {
		$this->id = $id;
	}

	public function getNome() {
		return $this->nome;
	}

	public function setNome($nome) {
		$this->nome = $nome;
	}

	public function getCelular() {
		return $this->celular;
	}

	public function setCelular($celular) {
		$this->celular = $celular;
	}

	public function getEmail() {
		return $this->email;
	}

	public function setEmail($email) {
		$this->email = $email;
	}

	public function getSenha() {
		return $this->senha;
	}

	public function setSenha($senha) {
		$this->senha = $senha;
	}

	public function getNivel() {
		return $this->nivel;
	}

	public function setNivel($nivel) {
		$this->nivel = $nivel;
	}

	public function getAlunos() {
		return $this->alunos;
	}

	public function setAlunos($alunos) {
		$this->alunos = $alunos;
	}

}
