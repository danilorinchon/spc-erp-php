<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: DELETE");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once "../../config/database.php";
include_once "../../models/Plans.php";

$database = new Database();
$db = $database->getConnection();

$plan = new Plans($db);

// Obter dados do DELETE
$data = json_decode(file_get_contents("php://input"));

if (!empty($data->id)) {
    $plan->id = $data->id;

    if ($plan->delete()) {
        echo json_encode(["message" => "Plano deletado com sucesso!"]);
    } else {
        echo json_encode(["message" => "Erro ao deletar plano."]);
    }
} else {
    echo json_encode(["message" => "Dados incompletos."]);
}
?>
