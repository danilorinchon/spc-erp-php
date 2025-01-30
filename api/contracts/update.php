<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: PUT");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once "../../config/database.php";
include_once "../../models/Contracts.php";

$database = new Database();
$db = $database->getConnection();

$contract = new Contracts($db);

// Obter dados do PUT
$data = json_decode(file_get_contents("php://input"));

if (!empty($data->id)) {
    $contract->id = $data->id;
    $contract->client_id = $data->client_id ?? null;
    $contract->plan_id = $data->plan_id ?? null;
    $contract->data_inicio = $data->data_inicio ?? null;
    $contract->data_fim = $data->data_fim ?? null;
    $contract->valor_total = $data->valor_total ?? null;
    $contract->desconto = $data->desconto ?? null;
    $contract->status = $data->status ?? null;

    // Verificar se o contrato existe antes de atualizar
    if (!$contract->existeContrato()) {
        echo json_encode(["message" => "Erro: Contrato não encontrado."]);
        exit();
    }

    if ($contract->update()) {
        echo json_encode(["message" => "Contrato atualizado com sucesso!"]);
    } else {
        echo json_encode(["message" => "Erro ao atualizar contrato."]);
    }
} else {
    echo json_encode(["message" => "Erro: ID do contrato não informado."]);
}
?>
