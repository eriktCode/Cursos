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
