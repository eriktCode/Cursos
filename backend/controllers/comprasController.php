<?php
require_once(__DIR__ . '/../models/compras.php');

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];

// Crear compra
if ($method === 'POST') {
    // Leer el cuerpo del request JSON
    $input = json_decode(file_get_contents("php://input"), true);

    if (!$input) {
        http_response_code(400);
        echo json_encode(['error' => 'No se recibieron datos válidos']);
        exit;
    }

    // Ejecutar la función para realizar compra
    $resultado = Compras::realizarCompra($input);
    $res_json = json_decode($resultado, true);

    if (isset($res_json['error'])) {
        http_response_code(400);
        echo json_encode($res_json);
    } else {
        http_response_code(201);
        echo json_encode($res_json);
    }
} else {
    http_response_code(405); // Método no permitido
    echo json_encode(['error' => 'Método no permitido']);
}
?>
