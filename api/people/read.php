<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

include_once "../../config/database.php";
include_once "../../models/People.php";

$database = new Database();
$db = $database->getConnection();

$people = new People($db);
$stmt = $people->read();

$num = $stmt->rowCount();

if ($num > 0) {
    $people_arr = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        extract($row);
        $people_item = [
            "id" => $id,
            "nome" => $nome,
            "cpf" => $cpf,
            "email" => $email,
            "telefone" => $telefone,
            "status" => $status
        ];
        array_push($people_arr, $people_item);
    }
    echo json_encode($people_arr);
} else {
    echo json_encode(["message" => "Nenhum usuário encontrado."]);
}
?>