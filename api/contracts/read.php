<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

include_once "../../config/database.php";
include_once "../../models/Contracts.php";

$database = new Database();
$db = $database->getConnection();

$contract = new Contracts($db);

$id = isset($_GET['id']) ? intval($_GET['id']) : null;

if ($id) {
    // Buscar um contrato específico
    $stmt = $contract->readOne($id);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($row) {
        echo json_encode($row);
    } else {
        echo json_encode(["message" => "Erro: Contrato não encontrado."]);
    }
} else {
    // Buscar todos os contratos
    $stmt = $contract->read();
    $num = $stmt->rowCount();

    if ($num > 0) {
        $contracts_arr = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            array_push($contracts_arr, $row);
        }
        echo json_encode($contracts_arr);
    } else {
        echo json_encode(["message" => "Nenhum contrato encontrado."]);
    }
}
?>
