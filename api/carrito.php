<?php
require_once(__DIR__ . '/../models/carrito.php');

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];
$requestUri = $_SERVER['REQUEST_URI'];
$scriptName = $_SERVER['SCRIPT_NAME'];

$action = '';
if (strpos($requestUri, $scriptName) === 0) {
    $action = substr($requestUri, strlen($scriptName));
    $action = trim($action, "/");
}

$action = explode('?', $action)[0]; // Limpiar query strings
$input = json_decode(file_get_contents("php://input"), true);

// Función de respuesta rápida
function respuestaJson($data, $codigo) {
    http_response_code($codigo);
    echo json_encode($data);
    exit;
}

switch ($action) {

    case 'agregarAlCarrito':
        if ($method !== 'POST') {
            respuestaJson(['error' => 'Método no permitido'], 405);
        }

        if (!isset($input['email'], $input['id_curso'])) {
            respuestaJson(['error' => 'Faltan email o id_curso'], 400);
        }

        $resultado = Carrito::agregarAlCarrito($input);
        $res_json = json_decode($resultado, true);

        if (isset($res_json['error'])) {
            respuestaJson($res_json, 400);
        } else {
            respuestaJson($res_json, 201);
        }
        break;

    case 'obtenerCarrito':
    if ($method !== 'GET') {
        respuestaJson(['error' => 'Método no permitido'], 405);
    }

    // Obtener el email desde el encabezado
    $headers = getallheaders();
    $email = $headers['email'] ?? $headers['Email'] ?? null;

    if (!$email) {
        respuestaJson(['error' => 'Falta el email en el encabezado'], 400);
    }

    $resultado = Carrito::obtenerCarrito($email);
    $res_json = json_decode($resultado, true);

    if (isset($res_json['error'])) {
        respuestaJson($res_json, 400);
    } else {
        respuestaJson($res_json, 200);
    }
    break;

    case 'eliminarDelCarrito':
        if ($method !== 'DELETE') {
            respuestaJson(['error' => 'Método no permitido'], 405);
        }

        if (!isset($input['email'], $input['id_curso'])) {
            respuestaJson(['error' => 'Faltan email o id_curso'], 400);
        }

        $resultado = Carrito::eliminarDelCarrito($input['email'], $input['id_curso']);
        $res_json = json_decode($resultado, true);

        if (isset($res_json['error'])) {
            respuestaJson($res_json, 400);
        } else {
            respuestaJson($res_json, 200);
        }
        break;

    case 'vaciarCarrito':
        if ($method !== 'DELETE') {
            respuestaJson(['error' => 'Método no permitido'], 405);
        }

        if (!isset($input['email'])) {
            respuestaJson(['error' => 'Falta el email'], 400);
        }

        $resultado = Carrito::vaciarCarrito($input['email']);
        $res_json = json_decode($resultado, true);

        if (isset($res_json['error'])) {
            respuestaJson($res_json, 400);
        } else {
            respuestaJson($res_json, 200);
        }
        break;

    default:
        respuestaJson(['error' => 'Ruta o acción no válida'], 404);
}
