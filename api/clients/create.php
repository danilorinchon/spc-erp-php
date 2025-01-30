<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once "../../config/database.php";
include_once "../../models/Clients.php";

// Conectar ao banco de dados
$database = new Database();
$db = $database->getConnection();

$client = new Clients($db);

// Obter dados do POST
$data = json_decode(file_get_contents("php://input"));

if (!empty($data->person_id) || !empty($data->company_id)) {
    $client->person_id = $data->person_id ?? NULL;
    $client->company_id = $data->company_id ?? NULL;
    $client->status = "ativo";

    if ($client->create()) {
        echo json_encode(["message" => "Cliente criado com sucesso!"]);
    } else {
        echo json_encode(["message" => "Erro ao criar cliente."]);
    }
} else {
    echo json_encode(["message" => "Dados incompletos."]);
}
?>
