<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: PUT");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once "../../config/database.php";
include_once "../../models/Plans.php";

$database = new Database();
$db = $database->getConnection();

$plan = new Plans($db);

// Obter dados do PUT
$data = json_decode(file_get_contents("php://input"));

if (!empty($data->id)) {
    $plan->id = $data->id;
    $plan->nome = $data->nome ?? "";
    $plan->descricao = $data->descricao ?? "";
    $plan->valor = $data->valor ?? 0.00;
    $plan->duracao = $data->duracao ?? 0;
    $plan->status = $data->status ?? "em_desenvolvimento";

    if ($plan->update()) {
        echo json_encode(["message" => "Plano atualizado com sucesso!"]);
    } else {
        echo json_encode(["message" => "Erro ao atualizar plano."]);
    }
} else {
    echo json_encode(["message" => "Dados incompletos."]);
}
?>
