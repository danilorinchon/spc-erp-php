<?php
class Clients {
    private $conn;
    private $table_name = "clients";

    public $id;
    public $person_id;
    public $company_id;
    public $status;
    public $created_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Listar todos os clientes
    public function read() {
        $query = "SELECT c.id, p.nome AS pessoa, cmp.razao_social AS empresa, c.status
                  FROM " . $this->table_name . " c
                  LEFT JOIN people p ON c.person_id = p.id
                  LEFT JOIN companies cmp ON c.company_id = cmp.id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }


// novo get por id
public function readOne($id) {
    $query = "SELECT c.id, p.nome AS pessoa, cmp.razao_social AS empresa, c.status
              FROM " . $this->table_name . " c
              LEFT JOIN people p ON c.person_id = p.id
              LEFT JOIN companies cmp ON c.company_id = cmp.id
              WHERE c.id = :id";
    
    $stmt = $this->conn->prepare($query);
    $stmt->bindParam(":id", $id, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt;
}

// novo put
public function update() {
    // Verificar se pelo menos um dos IDs foi informado
    if (empty($this->person_id) && empty($this->company_id)) {
        echo json_encode(["message" => "Erro: É necessário fornecer um `person_id` ou `company_id`."]);
        return false;
    }

    // Verificar se o `person_id` existe na tabela `people`
    if (!empty($this->person_id)) {
        $query_check_person = "SELECT id FROM people WHERE id = :person_id";
        $stmt_check_person = $this->conn->prepare($query_check_person);
        $stmt_check_person->bindParam(":person_id", $this->person_id, PDO::PARAM_INT);
        $stmt_check_person->execute();
        if ($stmt_check_person->rowCount() == 0) {
            echo json_encode(["message" => "Erro: `person_id` não encontrado na tabela `people`."]);
            return false;
        }
    }

    // Verificar se o `company_id` existe na tabela `companies`
    if (!empty($this->company_id)) {
        $query_check_company = "SELECT id FROM companies WHERE id = :company_id";
        $stmt_check_company = $this->conn->prepare($query_check_company);
        $stmt_check_company->bindParam(":company_id", $this->company_id, PDO::PARAM_INT);
        $stmt_check_company->execute();
        if ($stmt_check_company->rowCount() == 0) {
            echo json_encode(["message" => "Erro: `company_id` não encontrado na tabela `companies`."]);
            return false;
        }
    }

    // Query para atualizar o cliente
    $query = "UPDATE " . $this->table_name . " 
              SET person_id = :person_id, 
                  company_id = :company_id, 
                  status = :status
              WHERE id = :id";

    $stmt = $this->conn->prepare($query);

    $stmt->bindParam(":person_id", $this->person_id, PDO::PARAM_INT);
    $stmt->bindParam(":company_id", $this->company_id, PDO::PARAM_INT);
    $stmt->bindParam(":status", $this->status);
    $stmt->bindParam(":id", $this->id, PDO::PARAM_INT);

    if ($stmt->execute()) {
        return true;
    }
    return false;
}

// Verficar se o cliente existe. 
// Agora a função retorna true se encontrar pelo menos 1 cliente.
// evitando problemas ao contar registros usando rowCount()
public function exists($id) {
    $query = "SELECT COUNT(*) as total FROM " . $this->table_name . " WHERE id = :id";
    $stmt = $this->conn->prepare($query);
    $stmt->bindParam(":id", $id, PDO::PARAM_INT);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    return $result['total'] > 0;
}

// Deleta cliente com base no id
public function deleteClient() { // Renomeado para evitar conflito
    $query = "DELETE FROM " . $this->table_name . " WHERE id = :id";
    $stmt = $this->conn->prepare($query);
    $stmt->bindParam(":id", $this->id, PDO::PARAM_INT);

    return $stmt->execute();
}

// Novo metodo create
public function create() {
    // Verificar se o cliente existe
    $query_check_client = "SELECT id FROM clients WHERE id = :client_id";
    $stmt_check_client = $this->conn->prepare($query_check_client);
    $stmt_check_client->bindParam(":client_id", $this->client_id, PDO::PARAM_INT);
    $stmt_check_client->execute();
    if ($stmt_check_client->rowCount() == 0) {
        echo json_encode(["message" => "Erro: Cliente não encontrado."]);
        return false;
    }

    // Verificar se o plano existe
    $query_check_plan = "SELECT id FROM plans WHERE id = :plan_id";
    $stmt_check_plan = $this->conn->prepare($query_check_plan);
    $stmt_check_plan->bindParam(":plan_id", $this->plan_id, PDO::PARAM_INT);
    $stmt_check_plan->execute();
    if ($stmt_check_plan->rowCount() == 0) {
        echo json_encode(["message" => "Erro: Plano não encontrado."]);
        return false;
    }

    // Inserção do contrato
    $query = "INSERT INTO " . $this->table_name . " 
              (client_id, plan_id, data_inicio, data_fim, valor_total, desconto, status) 
              VALUES (:client_id, :plan_id, :data_inicio, :data_fim, :valor_total, :desconto, :status)";

    $stmt = $this->conn->prepare($query);

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

// Valida se um cliente é ativo
public function isActive($id) {
    $query = "SELECT status FROM " . $this->table_name . " WHERE id = :id";
    $stmt = $this->conn->prepare($query);
    $stmt->bindParam(":id", $id, PDO::PARAM_INT);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    return $result['status'] === 'ativo';
}

// Verifica se o cliente tem contrato ativo
public function hasActiveContract($client_id) {
    $query = "SELECT COUNT(*) as total FROM contracts WHERE client_id = :client_id AND status = 'ativo'";
    $stmt = $this->conn->prepare($query);
    $stmt->bindParam(':client_id', $client_id);
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    return ($row['total'] > 0); // Retorna true se o cliente tem um contrato ativo
}

}
?>
