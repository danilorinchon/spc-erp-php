<?php
include_once "Clients.php";
include_once "../config/database.php";

$database = new Database();
$db = $database->getConnection();

$client = new Clients($db);

// Defina um ID de cliente para testar
$test_client_id = 2;

if ($client->exists($test_client_id)) {
    echo "O cliente com ID $test_client_id existe no banco de dados.";
} else {
    echo "Erro: Cliente com ID $test_client_id NÃO encontrado no banco de dados.";
}
?>
