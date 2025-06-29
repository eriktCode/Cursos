<?php
session_start(); // Añade esto al inicio
require_once(__DIR__ . '/../models/clientes.php');

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);

    if (!isset($input['email']) || !isset($input['llave_secreta'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Faltan el email o la llave_secreta']);
        exit;
    }

    $resultado = Clientes::login($input['email'], $input['llave_secreta']);
    $res_json = json_decode($resultado, true);

    if (is_array($res_json) && count($res_json) > 0) {
        $_SESSION['usuario'] = $res_json[0]; // Guarda los datos del usuario en sesión
        http_response_code(200);
        echo json_encode([
            'mensaje' => 'Login exitoso', 
            'cliente' => $res_json[0],
            'redirect' => '/frontend/views/cursos.php' // Envía la ruta correcta
        ]);
    } else {
        http_response_code(401);
        echo json_encode(['error' => 'Credenciales inválidas']);
    }
} else {
    http_response_code(405);
    echo json_encode(['error' => 'Método no permitido']);
}