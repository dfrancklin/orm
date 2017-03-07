<?php
namespace App\Models\GreeningU;

/**
 * @ORM/Entity
 * @ORM/Table(name=postagem)
 */
class Post {

    /**
     * @ORM/Id
     * @ORM/Generated
     * @ORM/Column(type=int)
     */
    private $id;

    /**
     * @ORM/Column(type=string, lenght=20)
     */
    private $titulo;

    /**
     * @ORM/Column(type=string, lenght=100)
     */
    private $descricao;
    
    /**
     * @ORM/Column(type=lob)
     */
    private $imagem;
    
    /**
     * @ORM/Column(name=data_postagem, type=datetime)
     */
    private $data;

    /**
     * @ORM/HasMany(class=App\Models\GreeningU\Voto)
     */
    private $votos;

    /**
     * @ORM/BelongsTo(class=App\Models\GreeningU\Usuario)
     */
    private $usuario;

    /**
     * @ORM/BelongsTo(class=App\Models\GreeningU\Comunidade)
     */
    private $comunidade;

    public function getId() {
        return $this->id;
    }
    
    public function setId($id) {
        $this->id = $id;
    }
    
    public function getTitulo() {
        return $this->titulo;
    }
    
    public function setTitulo($titulo) {
        $this->titulo = $titulo;
    }
    
    public function getDescricao() {
        return $this->descricao;
    }
    
    public function setDescricao($descricao) {
        $this->descricao = $descricao;
    }
    
    public function getImagem() {
        return $this->imagem;
    }
    
    public function setImagem($imagem) {
        $this->imagem = $imagem;
    }
    
    public function getData() {
        return $this->data;
    }
    
    public function setData($data) {
        $this->data = $data;
    }

    public function getVotos() {
        return $this->votos;
    }
    
    public function setVotos($votos) {
        $this->votos = $votos;
    }

    public function getUsuario() {
        return $this->usuario;
    }
    
    public function setUsuario($usuario) {
        $this->usuario = $usuario;
    }
    
    public function getComunidade() {
        return $this->comunidade;
    }
    
    public function setComunidade($comunidade) {
        $this->comunidade = $comunidade;
    }

}