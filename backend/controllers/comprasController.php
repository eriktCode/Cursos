<?php
require_once(__DIR__ . '/../models/compras.php');

header('Content-Type: application/json');

try {
    $method = $_SERVER['REQUEST_METHOD'];
    $input = json_decode(file_get_contents('php://input'), true);

    if ($method === 'POST') {
        if (!$input || json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('Datos JSON inválidos');
        }

        if (empty($input['email']) || !isset($input['total']) || !isset($input['id_metodo_pago'])) {
            throw new Exception('Faltan campos obligatorios: email, total o id_metodo_pago');
        }

        $resultado = Compras::realizarCompra($input);
        $res_json = json_decode($resultado, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('Error al decodificar respuesta del modelo');
        }

        if (isset($res_json['error'])) {
            http_response_code(400);
            echo json_encode(['error' => $res_json['error']]);
        } else {
            http_response_code(201);
            echo json_encode($res_json);
        }
    } else {
        http_response_code(405);
        echo json_encode(['error' => 'Método no permitido']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Error en el servidor',
        'details' => $e->getMessage()
    ]);
}
?>