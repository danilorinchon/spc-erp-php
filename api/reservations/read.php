<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

include_once "../../config/database.php";
include_once "../../models/Reservations.php";

$database = new Database();
$db = $database->getConnection();

$reservation = new Reservations($db);

$id = isset($_GET['id']) ? intval($_GET['id']) : null;

if ($id) {
    // Buscar uma reserva específica
    $stmt = $reservation->readOne($id);
} else {
    // Buscar todas as reservas
    $stmt = $reservation->read();
}

$num = $stmt->rowCount();

if ($num > 0) {
    $reservations_arr = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        extract($row);
        $reservation_item = [
            "id" => $id,
            "space_id" => $space_id,
            "client_id" => $client_id,
            "data_reserva" => $data_reserva,
            "hora_inicio" => $hora_inicio,
            "hora_fim" => $hora_fim,
            "valor_total" => $valor_total,
            "status" => $status
        ];
        array_push($reservations_arr, $reservation_item);
    }
    echo json_encode($reservations_arr);
} else {
    echo json_encode(["message" => "Nenhuma reserva encontrada."]);
}
?>
