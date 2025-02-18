<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once '../../config/Database.php';
include_once '../../models/Payments.php';

$database = new Database();
$db = $database->getConnection();
$payment = new Payments($db);

$data = json_decode(file_get_contents("php://input"));

// Validar entrada obrigatória
if (!isset($data->contract_id) || !isset($data->amount) || !isset($data->payment_method)) {
    http_response_code(400);
    echo json_encode(["message" => "Erro: Todos os campos são obrigatórios!"]);
    exit;
}

// Se a forma de pagamento for ASAAS, chamamos a API externa
if ($data->payment_method === "asaas") {
    $asaas_url = "https://www.asaas.com/api/v3/payments"; // Endpoint ASAAS
    $asaas_api_key = "SUA_CHAVE_API_AQUI"; // Insira a chave correta

    // Se a chave da API ASAAS não foi configurada, retorna erro
    if ($asaas_api_key === "SUA_CHAVE_API_AQUI") {
        http_response_code(500);
        echo json_encode(["message" => "Erro: Chave da API ASAAS não configurada."]);
        exit;
    }

    // Buscar dados do cliente
    $query = "SELECT c.client_id, p.nome, p.email FROM contracts c
              JOIN clients cl ON c.client_id = cl.id
              JOIN people p ON cl.person_id = p.id
              WHERE c.id = :contract_id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':contract_id', $data->contract_id);
    $stmt->execute();
    $client = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$client) {
        http_response_code(404);
        echo json_encode(["message" => "Erro: Cliente não encontrado."]);
        exit;
    }

    // Criar payload ASAAS
    $payload = json_encode([
        "customer" => $client['client_id'],
        "billingType" => "BOLETO", // Pode ser 'PIX', 'BOLETO', 'CREDIT_CARD'
        "value" => $data->amount,
        "dueDate" => date('Y-m-d', strtotime("+7 days")),
        "description" => "Cobrança referente ao contrato #" . $data->contract_id
    ]);

    // Enviar requisição para ASAAS
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $asaas_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Content-Type: application/json",
        "access_token: $asaas_api_key"
    ]);

    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $asaas_response = json_decode($response, true);

    // Verificar se houve erro na requisição
    if ($http_code !== 200 || !isset($asaas_response['id'])) {
        http_response_code(500);
        echo json_encode([
            "message" => "Erro ao criar cobrança no ASAAS.",
            "error" => $asaas_response
        ]);
        exit;
    }

    // Inserir pagamento no banco apenas se a cobrança for bem-sucedida
    $payment->contract_id = $data->contract_id;
    $payment->amount = $data->amount;
    $payment->payment_method = "asaas";
    $payment->status = "pendente";
    $payment->transaction_id = $asaas_response['id'];

    if ($payment->create()) {
        http_response_code(201);
        echo json_encode([
            "message" => "Cobrança criada com sucesso!",
            "asaas_id" => $asaas_response['id']
        ]);
    } else {
        http_response_code(500);
        echo json_encode(["message" => "Erro ao registrar pagamento local."]);
    }
} else {
    http_response_code(400);
    echo json_encode(["message" => "Forma de pagamento inválida."]);
}
