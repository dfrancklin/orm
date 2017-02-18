<?php
//	namespace App\Model;

    /**
     * @ORM\@Entity
     * @ORM\@Table(name=responsaveis)
     */
	class Responsavel {

        /**
         * @ORM\@Id
         * @ORM\@Generated
         * @ORM\@Column(type=integer, name=id_resp)
         */
		private $id;

        /**
         * @ORM\@Column(name=nome_resp)
         */
		private $nome;

        /**
         * @ORM\@Column(name=cel_resp)
         */
		private $celular;

        /**
         * @ORM\@Column(name=email_resp, unique=true)
         */
		private $email;

		private $senha;

		private $nivel;

        /**
         * @ORM\@ManyToMany(class=Aluno)
         * @ORM\@JoinTable(tableName=responsavel_aluno,
         *					joinColumns={@JoinColumn(name=resp_id, referencedColumnName=id_resp)},
         *					inverseJoinColumns={@JoinColumn(name=aluno_id, referencedColumnName=matricula)})
         */
		private $alunos;

		/**
		 * @return mixed
		 */
		public function getId() {
			return $this->id;
		}

		/**
		 * @param mixed $id
		 */
		public function setId($id) {
			$this->id = $id;
		}

		/**
		 * @return mixed
		 */
		public function getNome() {
			return $this->nome;
		}

		/**
		 * @param mixed $nome
		 */
		public function setNome($nome) {
			$this->nome = $nome;
		}

		/**
		 * @return mixed
		 */
		public function getCelular() {
			return $this->celular;
		}

		/**
		 * @param mixed $celular
		 */
		public function setCelular($celular) {
			$this->celular = $celular;
		}

		/**
		 * @return mixed
		 */
		public function getEmail() {
			return $this->email;
		}

		/**
		 * @param mixed $email
		 */
		public function setEmail($email) {
			$this->email = $email;
		}

		/**
		 * @return mixed
		 */
		public function getSenha() {
			return $this->senha;
		}

		/**
		 * @param mixed $senha
		 */
		public function setSenha($senha) {
			$this->senha = $senha;
		}

		/**
		 * @return mixed
		 */
		public function getNivel() {
			return $this->nivel;
		}

		/**
		 * @param mixed $nivel
		 */
		public function setNivel($nivel) {
			$this->nivel = $nivel;
		}

		/**
		 * @return mixed
		 */
		public function getAlunos() {
			return $this->alunos;
		}

		/**
		 * @param mixed $alunos
		 */
		public function setAlunos($alunos) {
			$this->alunos = $alunos;
		}

	}
