<?php
require_once(__DIR__ . '/../models/compras.php');

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];
$requestUri = $_SERVER['REQUEST_URI'];
$scriptName = $_SERVER['SCRIPT_NAME'];

// Obtener acción desde la URL
$action = '';
if (strpos($requestUri, $scriptName) === 0) {
    $action = substr($requestUri, strlen($scriptName));
    $action = trim($action, "/");
}

$action = explode('?', $action)[0]; // Limpiar query strings

// Leer el body JSON una vez
$input = json_decode(file_get_contents("php://input"), true);

// Función para respuesta rápida
function respuestaJson($data, $codigo) {
    http_response_code($codigo);
    echo json_encode($data);
    exit;
}

// Ruteo
switch ($action) {
    case 'crearCompra':
        if ($method !== 'POST') {
            respuestaJson(['error' => 'Método no permitido'], 405);
        }

        if (!$input) {
            respuestaJson(['error' => 'No se recibieron datos válidos'], 400);
        }

        $resultado = Compras::realizarCompra($input);
        $res_json = json_decode($resultado, true);

        if (isset($res_json['error'])) {
            respuestaJson($res_json, 400);
        } else {
            respuestaJson($res_json, 201);
        }
        break;

    default:
        respuestaJson(['error' => 'Ruta o acción no válida'], 404);
}
