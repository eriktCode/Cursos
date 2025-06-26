<?php
require_once(__DIR__ . '/../models/carrito.php');

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];
$input = json_decode(file_get_contents("php://input"), true);

// Validar que se reciba JSON válido
if (!$input || !isset($input['email'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Falta el email en el cuerpo']);
    exit;
}

// Método POST - Agregar al carrito
if ($method === 'POST' && isset($input['id_curso'])) {
    $resultado = Carrito::agregarAlCarrito($input);
    $res_json = json_decode($resultado, true);

    if (isset($res_json['error'])) {
        http_response_code(400);
    } else {
        http_response_code(201);
    }

    echo json_encode($res_json);

// Método GET - Obtener carrito
} elseif ($method === 'GET') {
    $resultado = Carrito::obtenerCarrito($input['email']);
    $res_json = json_decode($resultado, true);

    if (isset($res_json['error'])) {
        http_response_code(400);
    } else {
        http_response_code(200);
    }

    echo json_encode($res_json);

// Método DELETE - Eliminar curso específico del carrito
} elseif ($method === 'DELETE' && isset($input['id_curso'])) {
    $resultado = Carrito::eliminarDelCarrito($input['email'], $input['id_curso']);
    $res_json = json_decode($resultado, true);

    if (isset($res_json['error'])) {
        http_response_code(400);
    } else {
        http_response_code(200);
    }

    echo json_encode($res_json);

// Método DELETE - Vaciar carrito completo
} elseif ($method === 'DELETE') {
    $resultado = Carrito::vaciarCarrito($input['email']);
    $res_json = json_decode($resultado, true);

    if (isset($res_json['error'])) {
        http_response_code(400);
    } else {
        http_response_code(200);
    }

    echo json_encode($res_json);

// Método no permitido
} else {
    http_response_code(405);
    echo json_encode(['error' => 'Método no permitido o parámetros insuficientes']);
}
?>
