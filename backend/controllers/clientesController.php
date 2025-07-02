<?php
require_once(__DIR__ . '/../models/clientes.php');

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $resultado = Clientes::obtenerClientes();
    $res_json = json_decode($resultado, true);

    if (isset($res_json['curl_error'])) {
        http_response_code(500);
        echo json_encode(['error' => 'Error de conexión: ' . $res_json['curl_error']]);
    } elseif ($res_json && $resultado !== '[]') {
        http_response_code(200);
        echo $resultado;
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'No se encontraron clientes']);
    }
} elseif ($method === 'POST') {
    // Leer el cuerpo JSON del request
    $input = json_decode(file_get_contents("php://input"), true);

    if (!$input) {
        http_response_code(400);
        echo json_encode(['error' => 'Datos del cliente no válidos']);
        exit;
    }

    // Validar campos obligatorios
    $camposObligatorios = ['nombre', 'email', 'llave_secreta'];
    foreach ($camposObligatorios as $campo) {
        if (!isset($input[$campo]) || empty($input[$campo])) {
            http_response_code(400);
            echo json_encode(['error' => "El campo $campo es requerido"]);
            exit;
        }
    }

    $resultado = Clientes::crearCliente($input);
    $res_json = json_decode($resultado, true);

    if (isset($res_json['curl_error'])) {
        http_response_code(500);
        echo json_encode(['error' => 'Error al crear cliente: ' . $res_json['curl_error']]);
    } elseif (isset($res_json[0]['id'])) {
        http_response_code(201);
        echo json_encode([
            'mensaje' => 'Cliente creado correctamente',
            'data' => $res_json[0]
        ]);
    } else {
        http_response_code(400);
        echo json_encode(['error' => 'Error al crear el cliente', 'detalles' => $res_json]);
    }
} elseif ($method === 'DELETE') {
    // Leer el cuerpo JSON del request para obtener el ID
    $input = json_decode(file_get_contents("php://input"), true);

    if (!isset($input['id'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Se requiere el ID del cliente para eliminar']);
        exit;
    }

    $id = $input['id'];
    $resultado = Clientes::eliminarCliente($id);
    $res_json = json_decode($resultado, true);

    if (isset($res_json['curl_error'])) {
        http_response_code(500);
        echo json_encode(['error' => 'Error al eliminar cliente: ' . $res_json['curl_error']]);
    } elseif (is_array($res_json) && count($res_json) > 0) {
        http_response_code(200);
        echo json_encode(['mensaje' => 'Cliente eliminado correctamente', 'data' => $res_json]);
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'No se encontró el cliente para eliminar']);
    }
} else {
    http_response_code(405);
    echo json_encode(['error' => 'Método no permitido']);
}
?>