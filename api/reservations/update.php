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

// verificar se user_id está definido antes de chamar logChange()
if (!isset($data->user_id)) {
    echo json_encode(["message" => "Erro: ID do usuário não informado para registro do log."]);
    exit;
}

// Logs para auditoria
if ($reservation->update()) {
    // 🚨 Garantir que `user_id` está no JSON antes de chamar `logChange()`
    if (isset($data->user_id)) {
        $reservation->logChange(
            $reservation->id,
            $data->user_id,
            'alterado',
            'Reserva do espaço ' . $reservation->space_id . ' foi alterada para a data ' . $reservation->data_reserva
        );
    } else {
        echo json_encode(["message" => "Reserva atualizada, mas log não registrado: user_id ausente."]);
    }

    echo json_encode(["message" => "Reserva atualizada com sucesso!"]);
} else {
    echo json_encode(["message" => "Erro ao atualizar reserva."]);
}

?>
