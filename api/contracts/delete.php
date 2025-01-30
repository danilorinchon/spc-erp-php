<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: DELETE");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once "../../config/database.php";
include_once "../../models/Contracts.php";

$database = new Database();
$db = $database->getConnection();

$contract = new Contracts($db);

// Obter dados do DELETE
$data = json_decode(file_get_contents("php://input"));

if (!empty($data->id)) {
    $contract->id = $data->id;

    // Verificar se o contrato existe antes de excluir
    if (!$contract->existeContrato()) {
        echo json_encode(["message" => "Erro: Contrato não encontrado."]);
        exit();
    }

    if ($contract->delete()) {
        echo json_encode(["message" => "Contrato deletado com sucesso!"]);
    } else {
        echo json_encode(["message" => "Erro ao deletar contrato."]);
    }
} else {
    echo json_encode(["message" => "Erro: ID do contrato não informado."]);
}
?>
