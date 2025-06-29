<?php
require_once(__DIR__ . '/../models/carrito.php');

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];
$request_uri = $_SERVER['REQUEST_URI'];

// Determinar si es una petición a obtenerCarrito
$is_obtener_carrito = strpos($request_uri, '/obtenerCarrito') !== false;

// Obtener parámetros según el método y endpoint
if ($method === 'GET' && $is_obtener_carrito) {
    // Para GET /obtenerCarrito, aceptamos email en body (pruebas) o en URL (producción)
    $input = json_decode(file_get_contents("php://input"), true) ?? [];
    $email = $_GET['email'] ?? $input['email'] ?? null;
} else {
    // Para otros métodos, solo aceptamos body JSON
    $input = json_decode(file_get_contents("php://input"), true);
    $email = $input['email'] ?? null;
}

// Validar email
if (empty($email)) {
    http_response_code(400);
    echo json_encode(['error' => 'Se requiere el parámetro email']);
    exit;
}

try {
    if ($method === 'GET' && $is_obtener_carrito) {
        // Endpoint obtenerCarrito (compatible con body y URL params)
        $resultado = Carrito::obtenerCarrito($email);
        $res_json = json_decode($resultado, true);
        http_response_code(isset($res_json['error']) ? 400 : 200);
        echo json_encode($res_json);
        
    } elseif ($method === 'POST' && isset($input['id_curso'])) {
        // Agregar al carrito
        $resultado = Carrito::agregarAlCarrito($input);
        $res_json = json_decode($resultado, true);
        http_response_code(isset($res_json['error']) ? 400 : 201);
        echo json_encode($res_json);
        
    } elseif ($method === 'DELETE' && isset($input['id_curso'])) {
        // Eliminar item específico
        $resultado = Carrito::eliminarDelCarrito($email, $input['id_curso']);
        $res_json = json_decode($resultado, true);
        http_response_code(isset($res_json['error']) ? 400 : 200);
        echo json_encode($res_json);
        
    } elseif ($method === 'DELETE') {
        // Vaciar carrito completo
        $resultado = Carrito::vaciarCarrito($email);
        $res_json = json_decode($resultado, true);
        http_response_code(isset($res_json['error']) ? 400 : 200);
        echo json_encode($res_json);
        
    } else {
        http_response_code(405);
        echo json_encode(['error' => 'Método no permitido o parámetros insuficientes']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error interno del servidor: ' . $e->getMessage()]);
}
?>