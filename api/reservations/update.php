<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: PUT");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once "../../config/database.php";
include_once "../../models/Reservations.php";

$database = new Database();
$db = $database->getConnection();

$reservation = new Reservations($db);

$data = json_decode(file_get_contents("php://input"));

// 🚨 Verificar se a reserva existe antes de atualizar
if (!$reservation->exists($data->id)) {
    echo json_encode(["message" => "Erro: Reserva não encontrada."]);
    exit;
}

// Definir os novos valores da reserva
$reservation->id = $data->id;
$reservation->space_id = $data->space_id;
$reservation->client_id = $data->client_id;
$reservation->data_reserva = $data->data_reserva;
$reservation->hora_inicio = $data->hora_inicio;
$reservation->hora_fim = $data->hora_fim;
$reservation->valor_total = $data->valor_total;
$reservation->status = $data->status;

// Atualizar a reserva no banco
if ($reservation->update()) {
    echo json_encode(["message" => "Reserva atualizada com sucesso!"]);
} else {
    echo json_encode(["message" => "Erro ao atualizar reserva."]);
}
?>
