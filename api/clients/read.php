<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

include_once "../../config/database.php";
include_once "../../models/Clients.php";

$database = new Database();
$db = $database->getConnection();

$client = new Clients($db);

// Verifica se foi passado um ID como parâmetro na URL (ex: read.php?id=1)
$id = isset($_GET['id']) ? intval($_GET['id']) : null;

if ($id) {
    // Buscar um cliente específico
    $stmt = $client->readOne($id);
    $num = $stmt->rowCount();

    if ($num > 0) {
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        extract($row);

        $client_item = [
            "id" => $id,
            "pessoa" => $pessoa,
            "empresa" => $empresa,
            "status" => $status
        ];
        echo json_encode($client_item);
    } else {
        echo json_encode(["message" => "Cliente não encontrado."]);
    }
} else {
    // Buscar todos os clientes
    $stmt = $client->read();
    $num = $stmt->rowCount();

    if ($num > 0) {
        $clients_arr = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            extract($row);
            $client_item = [
                "id" => $id,
                "pessoa" => $pessoa,
                "empresa" => $empresa,
                "status" => $status
            ];
            array_push($clients_arr, $client_item);
        }
        echo json_encode($clients_arr);
    } else {
        echo json_encode(["message" => "Nenhum cliente encontrado."]);
    }
}
?>
