<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once "../../config/database.php";
include_once "../../models/Plans.php";

$database = new Database();
$db = $database->getConnection();

$plan = new Plans($db);

// Obter dados do POST
$data = json_decode(file_get_contents("php://input"));

if (!empty($data->nome) && isset($data->valor)) {
    $plan->nome = $data->nome;
    $plan->descricao = $data->descricao ?? "";
    $plan->valor = $data->valor;
    $plan->duracao = $data->duracao ?? 0;
    $plan->status = $data->status ?? "em_desenvolvimento";

    if ($plan->create()) {
        echo json_encode(["message" => "Plano criado com sucesso!"]);
    } else {
        echo json_encode(["message" => "Erro ao criar plano."]);
    }
} else {
    echo json_encode(["message" => "Dados incompletos."]);
}
?>
