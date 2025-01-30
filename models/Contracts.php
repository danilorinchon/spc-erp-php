<?php
class Contracts {
    private $conn;
    private $table_name = "contracts";

    public $id;
    public $client_id;
    public $plan_id;
    public $data_inicio;
    public $data_fim;
    public $valor_total;
    public $desconto;
    public $status;
    public $created_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Criar um novo contrato
    public function create() {
        $query = "INSERT INTO " . $this->table_name . " 
                  (client_id, plan_id, data_inicio, data_fim, valor_total, desconto, status) 
                  VALUES (:client_id, :plan_id, :data_inicio, :data_fim, :valor_total, :desconto, :status)";

        $stmt = $this->conn->prepare($query);

        // Sanitização
        $this->client_id = htmlspecialchars(strip_tags($this->client_id));
        $this->plan_id = htmlspecialchars(strip_tags($this->plan_id));
        $this->data_inicio = htmlspecialchars(strip_tags($this->data_inicio));
        $this->data_fim = htmlspecialchars(strip_tags($this->data_fim));
        $this->valor_total = htmlspecialchars(strip_tags($this->valor_total));
        $this->desconto = htmlspecialchars(strip_tags($this->desconto));
        $this->status = htmlspecialchars(strip_tags($this->status));

        // Bind dos parâmetros
        $stmt->bindParam(":client_id", $this->client_id, PDO::PARAM_INT);
        $stmt->bindParam(":plan_id", $this->plan_id, PDO::PARAM_INT);
        $stmt->bindParam(":data_inicio", $this->data_inicio);
        $stmt->bindParam(":data_fim", $this->data_fim);
        $stmt->bindParam(":valor_total", $this->valor_total);
        $stmt->bindParam(":desconto", $this->desconto);
        $stmt->bindParam(":status", $this->status);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Obter um contrato específico
    public function readOne($id) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt;
    }
    
    // Listar todos os contratos
    public function read() {
        $query = "SELECT * FROM " . $this->table_name;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    // Verifica se cliente existe no DB
    public function clienteExiste() {
        $query = "SELECT id FROM clients WHERE id = :client_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":client_id", $this->client_id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->rowCount() > 0;
    }
    
    // Verifica se plano existe no DB    
    public function planoExiste() {
        $query = "SELECT id FROM plans WHERE id = :plan_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":plan_id", $this->plan_id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->rowCount() > 0;
    }
    
    // Verifica se o contrato existe
    public function existeContrato() {
        $query = "SELECT id FROM " . $this->table_name . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $this->id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->rowCount() > 0;
    }
    
    // Atualiza um cliente específico
    public function update() {
    $query = "UPDATE " . $this->table_name . " 
              SET client_id = :client_id, 
                  plan_id = :plan_id, 
                  data_inicio = :data_inicio, 
                  data_fim = :data_fim, 
                  valor_total = :valor_total, 
                  desconto = :desconto, 
                  status = :status
              WHERE id = :id";

    $stmt = $this->conn->prepare($query);

    $stmt->bindParam(":client_id", $this->client_id, PDO::PARAM_INT);
    $stmt->bindParam(":plan_id", $this->plan_id, PDO::PARAM_INT);
    $stmt->bindParam(":data_inicio", $this->data_inicio);
    $stmt->bindParam(":data_fim", $this->data_fim);
    $stmt->bindParam(":valor_total", $this->valor_total);
    $stmt->bindParam(":desconto", $this->desconto);
    $stmt->bindParam(":status", $this->status);
    $stmt->bindParam(":id", $this->id, PDO::PARAM_INT);

    if ($stmt->execute()) {
        return true;
    }
    return false;
}


// Deletanto um cliente
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
