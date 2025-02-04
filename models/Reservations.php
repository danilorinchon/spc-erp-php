<?php
class Reservations {
    private $conn;
    private $table_name = "reservations";

    public $id;
    public $space_id;
    public $client_id;
    public $data_reserva;
    public $hora_inicio;
    public $hora_fim;
    public $status;
    public $valor_total;
    public $created_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Criando uma reserva
    public function create() {
        $query = "INSERT INTO " . $this->table_name . " 
                  (space_id, client_id, data_reserva, hora_inicio, hora_fim, status, valor_total) 
                  VALUES (:space_id, :client_id, :data_reserva, :hora_inicio, :hora_fim, :status, :valor_total)";
                  
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(":space_id", $this->space_id, PDO::PARAM_INT);
        $stmt->bindParam(":client_id", $this->client_id, PDO::PARAM_INT);
        $stmt->bindParam(":data_reserva", $this->data_reserva);
        $stmt->bindParam(":hora_inicio", $this->hora_inicio);
        $stmt->bindParam(":hora_fim", $this->hora_fim);
        $stmt->bindParam(":status", $this->status);
        $stmt->bindParam(":valor_total", $this->valor_total);

        return $stmt->execute();
    }

    // Buscando uma reserva específica por ID
    public function readOne($id) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt;
    }

    // Buscando todas as reservas existentes
    public function read() {
        $query = "SELECT * FROM " . $this->table_name;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }
    
    // Verificação de concorrência de horário antes de criar uma reserva
    public function verificarDisponibilidade() {
        $query = "SELECT MAX(hora_fim) AS ultima_hora_ocupada 
                  FROM " . $this->table_name . " 
                  WHERE space_id = :space_id 
                  AND data_reserva = :data_reserva
                  AND status IN ('pendente', 'confirmada')
                  AND ((hora_inicio < :hora_fim AND hora_fim > :hora_inicio))";
    
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":space_id", $this->space_id, PDO::PARAM_INT);
        $stmt->bindParam(":data_reserva", $this->data_reserva);
        $stmt->bindParam(":hora_inicio", $this->hora_inicio);
        $stmt->bindParam(":hora_fim", $this->hora_fim);
        
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
        if ($result && $result['ultima_hora_ocupada']) {
            return [
                "disponivel" => false,
                "proximo_horario" => $result['ultima_hora_ocupada']
            ];
        }
    
        return ["disponivel" => true];
    }
        
    // Verificar se o cliente existe antes de criar a reserva
    public function exists($id) {
        $query = "SELECT id FROM " . $this->table_name . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id, PDO::PARAM_INT);
        $stmt->execute();
    
        return $stmt->rowCount() > 0;
    }

    // Atualizando uma reserva por id
    public function update() {
        $query = "UPDATE " . $this->table_name . " 
                  SET space_id = :space_id, client_id = :client_id, data_reserva = :data_reserva, 
                      hora_inicio = :hora_inicio, hora_fim = :hora_fim, valor_total = :valor_total, status = :status
                  WHERE id = :id";
    
        $stmt = $this->conn->prepare($query);
    
        $stmt->bindParam(":id", $this->id, PDO::PARAM_INT);
        $stmt->bindParam(":space_id", $this->space_id, PDO::PARAM_INT);
        $stmt->bindParam(":client_id", $this->client_id, PDO::PARAM_INT);
        $stmt->bindParam(":data_reserva", $this->data_reserva);
        $stmt->bindParam(":hora_inicio", $this->hora_inicio);
        $stmt->bindParam(":hora_fim", $this->hora_fim);
        $stmt->bindParam(":valor_total", $this->valor_total);
        $stmt->bindParam(":status", $this->status);
    
        return $stmt->execute();
    }
    
    // Deletando uma reserva por id
    public function delete() {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = :id";
    
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $this->id, PDO::PARAM_INT);
    
        return $stmt->execute();
    }
    


}
?>
