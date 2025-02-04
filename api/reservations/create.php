<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

//require_once "../../config/database.php";
//require_once "../../models/Reservations.php";
//require_once "../../models/Clients.php";  // Garante que só será incluído uma vez

include_once "../../config/database.php";
include_once "../../models/Reservations.php"; 
include_once "../../models/Clients.php";


$database = new Database();
$db = $database->getConnection();

$reservation = new Reservations($db);
$client = new Clients($db);

// Obter dados do POST
$data = json_decode(file_get_contents("php://input"));

// 🚨 Validar se é um cliente avulso (não tem contrato)
$eh_cliente_avulso = !$client->hasActiveContract($data->client_id);

// 🚨 Se for cliente avulso, definir status de pagamento pendente
if ($eh_cliente_avulso) {
    $reservation->status = "pendente_pagamento";
    $reservation->prazo_pagamento = date("Y-m-d H:i:s", strtotime("+1 hour"));
}

// 🚀 Criar reserva normalmente
if ($reservation->create()) {
    echo json_encode(["message" => "Reserva criada com sucesso!"]);
} else {
    echo json_encode(["message" => "Erro ao criar reserva."]);
}


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

// 🚨 Validar se o cliente está ativo
if (!$client->isActive($data->client_id)) {
    echo json_encode(["message" => "Erro: Cliente inativo não pode realizar reservas."]);
    exit;
}

// 🚨 Validar se a reserva está dentro do horário comercial
$hora_abertura = "08:00:00";
$hora_fechamento = "20:00:00";
$data_reserva = new DateTime($data->data_reserva);
$dia_semana = $data_reserva->format("N"); // 1 = Segunda, 7 = Domingo

// 🚨 Bloquear reservas fora do horário comercial
if ($data->hora_inicio < $hora_abertura || $data->hora_fim > $hora_fechamento) {
    echo json_encode(["message" => "Erro: Reservas só são permitidas entre 08:00 e 20:00."]);
    exit;
}

// 🚨 Validar mínimo de 4h para finais de semana e feriados
if ($dia_semana >= 6) {
    $duracao = (strtotime($data->hora_fim) - strtotime($data->hora_inicio)) / 3600;
    if ($duracao < 4) {
        echo json_encode(["message" => "Erro: Reservas de fim de semana e feriados devem ter no mínimo 4h."]);
        exit;
    }
}



?>
