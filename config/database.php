<?php
class Database {
    private $host = "localhost";  // Servidor MySQL (XAMPP)
    private $db_name = "smartplace_db";  // Nome do banco de dados
    private $username = "root";  // Usuário padrão do MySQL no XAMPP
    private $password = "";  // Senha vazia no XAMPP por padrão
    public $conn;

    public function getConnection() {
        $this->conn = null;
        try {
            $this->conn = new PDO("mysql:host=" . $this->host . ";dbname=" . $this->db_name, $this->username, $this->password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $exception) {
            echo "Erro de conexão: " . $exception->getMessage();
        }
        return $this->conn;
    }
}
?>
