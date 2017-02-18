<?php
	/**
     * @ORM\@Entity
     */
	class Log {

	    /**
         * @ORM\@Id
         * @ORM\@Generated
         * @ORM\@Column(type=integer)
         */
		private $id;

        /**
         * @ORM\@Column(type=date)
         */
		private $data;

        /**
         * @ORM\@Column(type=time)
         */
		private $hora;

        /**
		 * @ORM\@ManyToOne(class=Ambiente)
		 * @ORM\@JoinColumn(name=ambiente, nullable=false)
		 */
        private $ambiente;

        /**
		 * @ORM\@ManyToOne(class=Aluno)
		 * @ORM\@JoinColumn(name=aluno, nullable=false)
		 */
        private $aluno;

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
		public function getData() {
			return $this->data;
		}

		/**
		 * @param mixed $data
		 */
		public function setData($data) {
			$this->data = $data;
		}

		/**
		 * @return mixed
		 */
		public function getHora() {
			return $this->hora;
		}

		/**
		 * @param mixed $hora
		 */
		public function setHora($hora) {
			$this->hora = $hora;
		}

		/**
		 * @return mixed
		 */
		public function getAmbiente() {
			return $this->ambiente;
		}

		/**
		 * @param mixed $ambiente
		 */
		public function setAmbiente($ambiente) {
			$this->ambiente = $ambiente;
		}

		/**
		 * @return mixed
		 */
		public function getAluno() {
			return $this->aluno;
		}

		/**
		 * @param mixed $aluno
		 */
		public function setAluno($aluno) {
			$this->aluno = $aluno;
		}

	}
