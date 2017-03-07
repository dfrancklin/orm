<?php
namespace App\Models\GreeningU;

/**
 * @ORM/Entity
 */
class Comunidade {

    /**
     * @ORM/Id
     * @ORM/Generated
     * @ORM/Column(type=int)
     */
    private $id;

    /**
     * @ORM/Column(type=string, length=45)
     */
    private $nome;

    /**
     * @ORM/Column(type=datetime)
     */
    private $data;

    /**
     * @ORM/BelongsTo(class=App\Models\GreeningU\Usuario)
     * @ORM/JoinColumn(name=usuario_lider)
     */
    private $usuario;

    /**
     * @ORM/HasMany(class=App\Models\GreeningU\Post)
     */
    private $posts;

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
    
    public function getPosts() {
        return $this->posts;
    }
    
    public function setPosts($posts) {
        $this->posts = $posts;
    }

}