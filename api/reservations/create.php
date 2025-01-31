<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once "../../config/database.php";
include_once "../../models/Reservations.php";

$database = new Database();
$db = $database->getConnection();

$reservation = new Reservations($db);

// Obter dados do POST
$data = json_decode(file_get_contents("php://input"));

if (!empty($data->space_id) && !empty($data->client_id) && !empty($data->data_reserva) && !empty($data->hora_inicio) && !empty($data->hora_fim) && isset($data->valor_total)) {
    $reservation->space_id = $data->space_id;
    $reservation->client_id = $data->client_id;
    $reservation->data_reserva = $data->data_reserva;
    $reservation->hora_inicio = $data->hora_inicio;
    $reservation->hora_fim = $data->hora_fim;
    $reservation->status = $data->status ?? "pendente";
    $reservation->valor_total = $data->valor_total;

    // Verifica disponibilidade antes de criar a reserva
    $disponibilidade = $reservation->verificarDisponibilidade();
    
    if ($disponibilidade['disponivel']) {
        if ($reservation->create()) {
            echo json_encode(["message" => "Reserva criada com sucesso!"]);
        } else {
            echo json_encode(["message" => "Erro ao criar reserva. Verifique os dados e tente novamente."]);
        }
    } else {
        echo json_encode([
            "message" => "Erro: O espaço já está reservado nesse período.",
            "proximo_horario_disponivel" => $disponibilidade['proximo_horario']
        ]);
    }
} else {
    echo json_encode(["message" => "Erro: Todos os campos obrigatórios devem ser preenchidos."]);
}
?>
