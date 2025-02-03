<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: DELETE");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once "../../config/database.php";
include_once "../../models/Clients.php";

$database = new Database();
$db = $database->getConnection();

$client = new Clients($db);

// Obter dados do DELETE
$data = json_decode(file_get_contents("php://input"));

if (!empty($data->id)) {
    $client->id = $data->id;

    // Verificar se o cliente existe antes de excluir
    if (!$client->exists()) {
        echo json_encode(["message" => "Erro: Cliente não encontrado."]);
        return;
    }

    if ($client->deleteClient()) {
        echo json_encode(["message" => "Cliente deletado com sucesso!"]);
    } else {
        echo json_encode(["message" => "Erro ao deletar cliente."]);
    }
} else {
    echo json_encode(["message" => "Dados incompletos."]);
}
?>
