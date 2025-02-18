<?php
class Payments {
    private $conn;
    private $table_name = "payments";

    // Propriedades do pagamento
    public $id;
    public $contract_id;
    public $amount;
    public $payment_method;
    public $status;
    public $transaction_id;
    public $created_at;

    // Construtor
    public function __construct($db) {
        $this->conn = $db;
    }

    // Criar pagamento no banco
    public function create() {
        $query = "INSERT INTO " . $this->table_name . " (contract_id, amount, payment_method, status, transaction_id) 
                  VALUES (:contract_id, :amount, :payment_method, :status, :transaction_id)";
        
        $stmt = $this->conn->prepare($query);

        // Limpar dados
        $this->contract_id = htmlspecialchars(strip_tags($this->contract_id));
        $this->amount = htmlspecialchars(strip_tags($this->amount));
        $this->payment_method = htmlspecialchars(strip_tags($this->payment_method));
        $this->status = htmlspecialchars(strip_tags($this->status));
        $this->transaction_id = !empty($this->transaction_id) ? htmlspecialchars(strip_tags($this->transaction_id)) : null;

        // Bind dos parâmetros
        $stmt->bindParam(":contract_id", $this->contract_id);
        $stmt->bindParam(":amount", $this->amount);
        $stmt->bindParam(":payment_method", $this->payment_method);
        $stmt->bindParam(":status", $this->status);
        $stmt->bindParam(":transaction_id", $this->transaction_id);

        // Executar
        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Buscar todos os pagamentos
    public function read() {
        $query = "SELECT * FROM " . $this->table_name . " ORDER BY created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    // Buscar um pagamento específico pelo ID
    public function readOne($id) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id = :id LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Atualizar status do pagamento (Exemplo: Pendente → Pago)
    public function updateStatus($id, $new_status) {
        $query = "UPDATE " . $this->table_name . " SET status = :status WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":status", $new_status);
        $stmt->bindParam(":id", $id);
        return $stmt->execute();
    }

    // Deletar um pagamento
    public function delete($id) {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        return $stmt->execute();
    }
}
?>
