<?php
namespace App\Models\GreeningU;

/**
 * @ORM/Entity
 */
class Usuario {

    /**
     * @ORM/Id
     * @ORM/Generated
     * @ORM/Column(type=int)
     */
    private $id;

    /**
     * @ORM/Column(type=string, length=20)
     */
    private $nome;

    /**
     * @ORM/Column(type=string, length=30)
     */
    private $sobrenome;

    /**
     * @ORM/Column(type=string, length=30)
     */
    private $email;

    /**
     * @ORM/Column(type=string, length=10)
     */
    private $login;

    /**
     * @ORM/Column(type=string, length=12)
     */
    private $senha;

    /**
     * @ORM/Column(type=string, length=1)
     */
    private $sexo;

    /**
     * @ORM/Column(type=int)
     */
    private $pontuacao;

    /**
     * @ORM/HasMany(class=App\Models\GreeningU\Comunidade)
     */
    private $comunidades;

    /**
     * @ORM/ManyToMany(class=App\Models\GreeningU\Comunidade)
     * @ORM/JoinTable(tableName=usuario_comunidade)
     */
    private $assinaturas;

    /**
     * @ORM/HasMany(class=App\Models\GreeningU\Post)
     */
    private $posts;

    /**
     * @ORM/HasMany(class=App\Models\GreeningU\Comentario)
     */
    private $comentarios;

    /**
     * @ORM/HasMany(class=App\Models\GreeningU\Voto)
     */
    private $votos;

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
    
    public function getSobrenome() {
        return $this->sobrenome;
    }
    
    public function setSobrenome($sobrenome) {
        $this->sobrenome = $sobrenome;
    }
    
    public function getEmail() {
        return $this->email;
    }
    
    public function setEmail($email) {
        $this->email = $email;
    }
    
    public function getLogin() {
        return $this->login;
    }
    
    public function setLogin($login) {
        $this->login = $login;
    }
    
    public function getSenha() {
        return $this->senha;
    }
    
    public function setSenha($senha) {
        $this->senha = $senha;
    }
    
    public function getSexo() {
        return $this->sexo;
    }
    
    public function setSexo($sexo) {
        $this->sexo = $sexo;
    }
    
    public function getPontuacao() {
        return $this->pontuacao;
    }
    
    public function setPontuacao($pontuacao) {
        $this->pontuacao = $pontuacao;
    }

    public function getComunidades() {
        return $this->comunidades;
    }
    
    public function setComunidades($comunidades) {
        $this->comunidades = $comunidades;
    }

    public function getAssinaturas() {
        return $this->assinaturas;
    }
    
    public function setAssinaturas($assinaturas) {
        $this->assinaturas = $assinaturas;
    }
    
    public function getPosts() {
        return $this->posts;
    }
    
    public function setPosts($posts) {
        $this->posts = $posts;
    }
    
    public function getComentarios() {
        return $this->comentarios;
    }
    
    public function setComentarios($comentarios) {
        $this->comentarios = $comentarios;
    }
    
    public function getVotos() {
        return $this->votos;
    }
    
    public function setVotos($votos) {
        $this->votos = $votos;
    }

}