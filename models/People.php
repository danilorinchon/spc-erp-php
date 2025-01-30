<?php
class People {
    private $conn;
    private $table_name = "people";

    public $id;
    public $nome;
    public $cpf;
    public $email;
    public $telefone;
    public $senha;
    public $oauth_provider;
    public $oauth_id;
    public $tipo_funcionario;
    public $status;
    public $created_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Método para criar um novo usuário
    public function create() {
        $query = "INSERT INTO " . $this->table_name . " (nome, cpf, email, telefone, senha, oauth_provider, oauth_id, tipo_funcionario, status) 
                  VALUES (:nome, :cpf, :email, :telefone, :senha, :oauth_provider, :oauth_id, :tipo_funcionario, :status)";

        $stmt = $this->conn->prepare($query);

        // Sanitização
        $this->nome = htmlspecialchars(strip_tags($this->nome));
        $this->cpf = htmlspecialchars(strip_tags($this->cpf));
        $this->email = htmlspecialchars(strip_tags($this->email));
        $this->telefone = htmlspecialchars(strip_tags($this->telefone));
        $this->senha = password_hash($this->senha, PASSWORD_BCRYPT);
        $this->oauth_provider = htmlspecialchars(strip_tags($this->oauth_provider));
        $this->oauth_id = htmlspecialchars(strip_tags($this->oauth_id));
        $this->tipo_funcionario = htmlspecialchars(strip_tags($this->tipo_funcionario));
        $this->status = htmlspecialchars(strip_tags($this->status));

        // Bind dos parâmetros
        $stmt->bindParam(":nome", $this->nome);
        $stmt->bindParam(":cpf", $this->cpf);
        $stmt->bindParam(":email", $this->email);
        $stmt->bindParam(":telefone", $this->telefone);
        $stmt->bindParam(":senha", $this->senha);
        $stmt->bindParam(":oauth_provider", $this->oauth_provider);
        $stmt->bindParam(":oauth_id", $this->oauth_id);
        $stmt->bindParam(":tipo_funcionario", $this->tipo_funcionario);
        $stmt->bindParam(":status", $this->status);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Método para listar todos os usuários
    public function read() {
        $query = "SELECT id, nome, cpf, email, telefone, status FROM " . $this->table_name;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function delete() {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
    
        $this->id = htmlspecialchars(strip_tags($this->id));
        $stmt->bindParam(":id", $this->id);
    
        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function update() {
        $query = "UPDATE " . $this->table_name . " 
                  SET nome = :nome, 
                      cpf = :cpf, 
                      email = :email, 
                      telefone = :telefone, 
                      status = :status
                  WHERE id = :id";
    
        $stmt = $this->conn->prepare($query);
    
        // Sanitização
        $this->nome = htmlspecialchars(strip_tags($this->nome));
        $this->cpf = htmlspecialchars(strip_tags($this->cpf));
        $this->email = htmlspecialchars(strip_tags($this->email));
        $this->telefone = htmlspecialchars(strip_tags($this->telefone));
        $this->status = htmlspecialchars(strip_tags($this->status));
        $this->id = htmlspecialchars(strip_tags($this->id));
    
        // Bind dos parâmetros
        $stmt->bindParam(":nome", $this->nome);
        $stmt->bindParam(":cpf", $this->cpf);
        $stmt->bindParam(":email", $this->email);
        $stmt->bindParam(":telefone", $this->telefone);
        $stmt->bindParam(":status", $this->status);
        $stmt->bindParam(":id", $this->id, PDO::PARAM_INT);
    
        if ($stmt->execute()) {
            return true;
        }
        
        return false;
    }
    

}
?>
