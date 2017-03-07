<?php
namespace App\Models\GreeningU;

/**
 * @ORM/Entity
 */
class Voto {

    /**
     * @ORM/Id
     * @ORM/Generated
     * @ORM/Column(type=int)
     */
    private $id;

    /**
     * @ORM/BelongsTo(class=App\Models\GreeningU\Usuario)
     * @ORM/JoinColumn(name=id_usuario_votador)
     */
    private $usuario;

    /**
     * @ORM/BelongsTo(class=App\Models\GreeningU\Post)
     * @ORM/JoinColumn(name=id_postagem)
     */
    private $post;
    
    /**
     * @ORM/Column(name=data_voto, type=datetime)
     */
    private $data;
    
    /**
     * @ORM/Column(type=int)
     */
    private $pontos;

    public function getId() {
        return $this->id;
    }
    
    public function setId($id) {
        $this->id = $id;
    }
    
    public function getUsuario() {
        return $this->usuario;
    }
    
    public function setUsuario($usuario) {
        $this->usuario = $usuario;
    }
    
    public function getData() {
        return $this->data;
    }
    
    public function setData($data) {
        $this->data = $data;
    }
    
    public function getPontos() {
        return $this->pontos;
    }
    
    public function setPontos($pontos) {
        $this->pontos = $pontos;
    }
    
}