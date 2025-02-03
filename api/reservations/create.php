<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

require_once "../../config/database.php";
require_once "../../models/Reservations.php";
require_once "../../models/Clients.php";  // Garante que só será incluído uma vez



$database = new Database();
$db = $database->getConnection();

$reservation = new Reservations($db);
$client = new Clients($db);

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

    // 🚨 Verificar se o intervalo mínimo da reserva é de 1 hora
    $inicio = new DateTime($reservation->hora_inicio);
    $fim = new DateTime($reservation->hora_fim);
    $intervalo = $inicio->diff($fim);
    $horas = $intervalo->h + ($intervalo->i / 60); // Converte minutos em fração de hora

    if ($horas < 1) {
        echo json_encode(["message" => "Erro: A reserva deve ter no mínimo 1 hora de duração."]);
        exit;
    }

    // 🚨 Validar se o cliente existe
    if (!$client->exists($data->client_id)) {
        echo json_encode(["message" => "Erro: Cliente não encontrado."]);
        exit;
    }

    // 🚨 Verifica disponibilidade antes de criar a reserva
    $disponibilidade = $reservation->verificarDisponibilidade();

    if ($disponibilidade['disponivel']) {
        if ($reservation->create()) {
            echo json_encode(["message" => "Reserva criada com sucesso!"]);
        } else {
            echo json_encode(["message" => "Erro ao criar reserva."]);
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
