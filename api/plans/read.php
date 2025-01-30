<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

include_once "../../config/database.php";
include_once "../../models/Plans.php";

$database = new Database();
$db = $database->getConnection();

$plan = new Plans($db);

$id = isset($_GET['id']) ? intval($_GET['id']) : null;

if ($id) {
    // Buscar um plano específico
    $stmt = $plan->readOne($id);
} else {
    // Buscar todos os planos
    $stmt = $plan->read();
}

$num = $stmt->rowCount();

if ($num > 0) {
    $plans_arr = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        extract($row);
        $plan_item = [
            "id" => $id,
            "nome" => $nome,
            "descricao" => $descricao,
            "valor" => $valor,
            "duracao" => $duracao,
            "status" => $status
        ];
        array_push($plans_arr, $plan_item);
    }
    echo json_encode($plans_arr);
} else {
    echo json_encode(["message" => "Nenhum plano encontrado."]);
}
?>
