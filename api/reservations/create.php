<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

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

} else {
/// verifica se o cliente possui um contrato ativo         
    if (!$client->hasActiveContract($data->client_id)) {
    echo json_encode(["message" => "Erro: Cliente não possui um contrato ativo e não pode fazer reservas."]);
    exit;
    }
}

// Regras para validação de horários
// Definição do horário comercial de segunda a sexta-feira (dias úteis)
$hora_abertura = "08:00:00";
$hora_fechamento = "20:00:00";

// Converter horários para comparação
$hora_inicio_reserva = strtotime($data->hora_inicio);
$hora_fim_reserva = strtotime($data->hora_fim);
$hora_abertura_sistema = strtotime($hora_abertura);
$hora_fechamento_sistema = strtotime($hora_fechamento);

// Obter a data da reserva para verificar se é dia útil ou final de semana
$data_reserva = new DateTime($data->data_reserva);
$dia_semana = $data_reserva->format("N"); // 1 = Segunda-feira, 7 = Domingo

// Verifica se é feriado
$feriados = ["2025-01-01", "2025-02-25", "2025-04-21", "2025-05-01", "2025-09-07", "2025-10-12", "2025-11-02", "2025-11-15", "2025-12-25"]; // Adicione os feriados da cidade de Osasco
$eh_feriado = in_array($data->data_reserva, $feriados);

// 🚨 Ajuste para reservas antes do horário de abertura (Segunda a Sexta-feira dias úteis)
if ($dia_semana >= 1 && $dia_semana <= 5 && !$eh_feriado && $hora_inicio_reserva < $hora_abertura_sistema) {
    $data->hora_fim = $hora_abertura;
    echo json_encode(["message" => "A reserva começa antes do horário de abertura da casa. O horário de término foi ajustado para $hora_abertura."]);
    exit;
}

// 🚨 Ajuste para reservas após o horário de fechamento (Segunda a Sexta-feira dias úteis)
if ($dia_semana >= 1 && $dia_semana <= 5 && !$eh_feriado && $hora_fim_reserva > $hora_fechamento_sistema) {
    $data->hora_inicio = $hora_fechamento;
    echo json_encode(["message" => "A reserva termina após o horário de expediente. O horário de início foi ajustado para $hora_fechamento."]);
    exit;
}

// 🚨 Validação para finais de semana e feriados (Mínimo de 4 horas)
if ($dia_semana >= 6 || $eh_feriado) {
    $duracao = ($hora_fim_reserva - $hora_inicio_reserva) / 3600;
    if ($duracao < 4) {
        echo json_encode(["message" => "Erro: Reservas de fim de semana e feriados devem ter no mínimo 4 horas."]);
        exit;
    }
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

// Logs para auditoria
if ($reservation->create()) {
    // 🚀 Adicionando log da criação da reserva
    $reservation->logChange($reservation->id, $data->user_id, 'criado', 
        'Reserva criada para o espaço ' . $reservation->space_id . ' na data ' . $reservation->data_reserva);

    echo json_encode(["message" => "Reserva criada com sucesso!"]);
} else {
    echo json_encode(["message" => "Erro ao criar reserva."]);
}


?>
