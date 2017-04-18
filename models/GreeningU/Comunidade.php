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
     */
    private $lider;

    /**
     * @ORM/ManyToMany(class=App\Models\GreeningU\Usuario, mappedBy=assinaturas)
     */
    private $usuarios;

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
    
    public function getLider() {
        return $this->lider;
    }
    
    public function setLider($lider) {
        $this->lider = $lider;
    }
    
    public function getUsuarios() {
        return $this->usuarios;
    }
    
    public function setUsuarios($usuarios) {
        $this->usuarios = $usuarios;
    }
    
    public function getPosts() {
        return $this->posts;
    }
    
    public function setPosts($posts) {
        $this->posts = $posts;
    }

}