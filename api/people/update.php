<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: PUT");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once "../../config/database.php";
include_once "../../models/People.php";

$database = new Database();
$db = $database->getConnection();

$people = new People($db);

// Obter dados do PUT
$data = json_decode(file_get_contents("php://input"));

if (!empty($data->id)) {
    $people->id = $data->id;
    $people->nome = $data->nome ?? NULL;
    $people->cpf = $data->cpf ?? NULL;
    $people->email = $data->email ?? NULL;
    $people->telefone = $data->telefone ?? NULL;
    $people->status = $data->status ?? "ativo";

    if ($people->update()) {
        echo json_encode(["message" => "Usuário atualizado com sucesso!"]);
    } else {
        echo json_encode(["message" => "Erro ao atualizar usuário."]);
    }
} else {
    echo json_encode(["message" => "Dados incompletos."]);
}
?>
