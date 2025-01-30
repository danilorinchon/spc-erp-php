<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once "../../config/database.php";
include_once "../../models/Contracts.php";

$database = new Database();
$db = $database->getConnection();

$contract = new Contracts($db);

// Obter dados do POST
$data = json_decode(file_get_contents("php://input"));

if (!empty($data->client_id) && !empty($data->plan_id) && !empty($data->data_inicio) && !empty($data->data_fim) && isset($data->valor_total)) {
    $contract->client_id = $data->client_id;
    $contract->plan_id = $data->plan_id;
    $contract->data_inicio = $data->data_inicio;
    $contract->data_fim = $data->data_fim;
    $contract->valor_total = $data->valor_total;
    $contract->desconto = $data->desconto ?? 0.00;
    $contract->status = $data->status ?? "pendente";

    // Validar se o cliente existe antes de tentar criar o contrato
    if (!$contract->clienteExiste()) {
        echo json_encode(["message" => "Erro: Cliente não encontrado."]);
        exit();
    }

    // Validar se o plano existe antes de tentar criar o contrato
    if (!$contract->planoExiste()) {
        echo json_encode(["message" => "Erro: Plano não encontrado."]);
        exit();
    }

    if ($contract->create()) {
        echo json_encode(["message" => "Contrato criado com sucesso!"]);
    } else {
        echo json_encode(["message" => "Erro ao criar contrato. Verifique os dados e tente novamente."]);
    }
} else {
    echo json_encode(["message" => "Erro: Todos os campos obrigatórios devem ser preenchidos."]);
}
?>
