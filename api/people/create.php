<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once "../../config/database.php";
include_once "../../models/People.php";

// Conectar ao banco de dados
$database = new Database();
$db = $database->getConnection();

$people = new People($db);

// Obter dados do POST
$data = json_decode(file_get_contents("php://input"));

if (!empty($data->nome) && !empty($data->cpf) && !empty($data->email) && !empty($data->senha)) {
    $people->nome = $data->nome;
    $people->cpf = $data->cpf ?? NULL;
    $people->email = $data->email;
    $people->telefone = $data->telefone ?? NULL;
    $people->senha = $data->senha;
    $people->oauth_provider = $data->oauth_provider ?? 'local';
    $people->oauth_id = $data->oauth_id ?? NULL;
    $people->tipo_funcionario = $data->tipo_funcionario ?? 'nenhum';
    $people->status = "ativo";

    if ($people->create()) {
        echo json_encode(["message" => "Usuário criado com sucesso!"]);
    } else {
        echo json_encode(["message" => "Erro ao criar usuário."]);
    }
} else {
    echo json_encode(["message" => "Dados incompletos."]);
}
?>
