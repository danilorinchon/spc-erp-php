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
    

}
?>
