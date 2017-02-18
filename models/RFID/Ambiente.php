<?php
	/**
     * @ORM\@Entity
     * @ORM\@Table(name=ambientes)
     */
	class Ambiente {

        /**
         * @ORM\@Id
         * @ORM\@Generated
         * @ORM\@Column(name=id_ambiente, type=integer)
         */
		private $id;

        /**
         * @ORM\@Column(name=id_leitor)
         */
		private $leitor;

        /**
         * @ORM\@Column(name=desc_ambiente)
         */
		private $descricao;

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
		public function getLeitor() {
			return $this->leitor;
		}

		/**
		 * @param mixed $leitor
		 */
		public function setLeitor($leitor) {
			$this->leitor = $leitor;
		}

		/**
		 * @return mixed
		 */
		public function getDescricao() {
			return $this->descricao;
		}

		/**
		 * @param mixed $descricao
		 */
		public function setDescricao($descricao) {
			$this->descricao = $descricao;
		}

	}
