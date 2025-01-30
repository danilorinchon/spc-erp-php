<?php
class Plans {
    private $conn;
    private $table_name = "plans";

    public $id;
    public $nome;
    public $descricao;
    public $valor; // Correção do nome do campo
    public $duracao;
    public $status;
    public $created_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Criar um novo plano
    public function create() {
        $query = "INSERT INTO " . $this->table_name . " (nome, descricao, valor, duracao, status) 
                  VALUES (:nome, :descricao, :valor, :duracao, :status)";

        $stmt = $this->conn->prepare($query);

        // Sanitização
        $this->nome = htmlspecialchars(strip_tags($this->nome));
        $this->descricao = htmlspecialchars(strip_tags($this->descricao));
        $this->valor = htmlspecialchars(strip_tags($this->valor));
        $this->duracao = htmlspecialchars(strip_tags($this->duracao));
        $this->status = htmlspecialchars(strip_tags($this->status));

        // Bind dos parâmetros
        $stmt->bindParam(":nome", $this->nome);
        $stmt->bindParam(":descricao", $this->descricao);
        $stmt->bindParam(":valor", $this->valor);
        $stmt->bindParam(":duracao", $this->duracao, PDO::PARAM_INT);
        $stmt->bindParam(":status", $this->status);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Listar todos os planos
    public function read() {
        $query = "SELECT * FROM " . $this->table_name;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    // Obter um plano específico
    public function readOne($id) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt;
    }

    // Atualizar um plano específico
    public function update() {
        $query = "UPDATE " . $this->table_name . " 
                  SET nome = :nome, descricao = :descricao, valor = :valor, duracao = :duracao, status = :status
                  WHERE id = :id";
    
        $stmt = $this->conn->prepare($query);
    
        $stmt->bindParam(":nome", $this->nome);
        $stmt->bindParam(":descricao", $this->descricao);
        $stmt->bindParam(":valor", $this->valor);
        $stmt->bindParam(":duracao", $this->duracao, PDO::PARAM_INT);
        $stmt->bindParam(":status", $this->status);
        $stmt->bindParam(":id", $this->id, PDO::PARAM_INT);
    
        if ($stmt->execute()) {
            return true;
        }
        return false;
    }
   
    // Excluir um plano específico
    public function delete() {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
    
        $stmt->bindParam(":id", $this->id, PDO::PARAM_INT);
    
        if ($stmt->execute()) {
            return true;
        }
        return false;
    }
    

}
?>
