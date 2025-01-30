<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: PUT");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once "../../config/database.php";
include_once "../../models/Clients.php";

$database = new Database();
$db = $database->getConnection();

$client = new Clients($db);

// Obter dados do PUT
$data = json_decode(file_get_contents("php://input"));

if (!empty($data->id)) {
    $client->id = $data->id;
    $client->person_id = $data->person_id ?? NULL;
    $client->company_id = $data->company_id ?? NULL;
    $client->status = $data->status ?? "ativo";

    if ($client->update()) {
        echo json_encode(["message" => "Cliente atualizado com sucesso!"]);
    } else {
        echo json_encode(["message" => "Erro ao atualizar cliente."]);
    }
} else {
    echo json_encode(["message" => "Dados incompletos."]);
}
?>
