<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: DELETE");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once "../../config/database.php";
include_once "../../models/People.php";

$database = new Database();
$db = $database->getConnection();

$people = new People($db);

// Obter dados do DELETE
$data = json_decode(file_get_contents("php://input"));

if (!empty($data->id)) {
    $people->id = $data->id;

    if ($people->delete()) {
        echo json_encode(["message" => "Usuário deletado com sucesso!"]);
    } else {
        echo json_encode(["message" => "Erro ao deletar usuário."]);
    }
} else {
    echo json_encode(["message" => "Dados incompletos."]);
}
?>
