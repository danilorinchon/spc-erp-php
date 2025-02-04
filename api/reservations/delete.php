<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once "../../config/database.php";
include_once "../../models/Reservations.php";
include_once "../../models/Clients.php";

$database = new Database();
$db = $database->getConnection();

$reservation = new Reservations($db);
$client = new Clients($db);

$data = json_decode(file_get_contents("php://input"));

// 🚨 Verificar se a reserva existe antes de excluir
if (!$reservation->exists($data->id)) {
    echo json_encode(["message" => "Erro: Reserva não encontrada."]);
    exit;
}

// 🔍 Buscar dados da reserva
$reservation->id = $data->id;
$reservaInfo = $reservation->getDetails($data->id);

// 🚨 Validar se a reserva pode ser cancelada
$data_atual = new DateTime();
$data_reserva = new DateTime($reservaInfo['data_reserva']);
$intervalo = $data_atual->diff($data_reserva)->format('%r%a'); // Dias entre hoje e a reserva

if ($intervalo < 1) {
    echo json_encode(["message" => "Erro: Cancelamento só permitido com 24h de antecedência."]);
    exit;
}

// 🚀 Excluir reserva
if ($reservation->delete()) {
    echo json_encode(["message" => "Reserva cancelada com sucesso!"]);
} else {
    echo json_encode(["message" => "Erro ao cancelar reserva."]);
}
?>
