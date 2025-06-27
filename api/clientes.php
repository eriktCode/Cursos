<?php
require_once(__DIR__ . '/../models/clientes.php');

header('Content-Type: application/json');

// Obtener la ruta después de /api/clientes.php
$path = $_SERVER['REQUEST_URI'];
$method = $_SERVER['REQUEST_METHOD'];

// Extraer la parte final de la URL para la acción
// Ejemplo: /api/clientes.php/obtenerClientes => obtenerClientes
// Nota: Ajusta según cómo configures el servidor para que pase la ruta correcta
$scriptName = $_SERVER['SCRIPT_NAME']; // /api/clientes.php
$requestUri = $_SERVER['REQUEST_URI']; // /api/clientes.php/obtenerClientes o /api/clientes.php?algo

// Si usas .htaccess o routing limpio, aquí tienes el path correcto:
// Vamos a obtener lo que venga después de clientes.php
$action = '';
if (strpos($requestUri, $scriptName) === 0) {
    $action = substr($requestUri, strlen($scriptName));
    $action = trim($action, "/");
}

// Para peticiones con query strings limpiar parámetros GET
$action = explode('?', $action)[0];

// Ruteo simple según acción y método HTTP
switch ($action) {
    case 'obtenerClientes':
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
        } else {
            http_response_code(405);
            echo json_encode(['error' => 'Método no permitido para obtenerClientes']);
        }
        break;

    case 'eliminarCliente':
    if ($method === 'DELETE') {
        $input = json_decode(file_get_contents("php://input"), true);
        if (!isset($input['email'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Se requiere el email del cliente para eliminar']);
            exit;
        }

        $email = $input['email'];
        $resultado = Clientes::eliminarCliente($email);
        $res_json = json_decode($resultado, true);

        if (isset($res_json['curl_error'])) {
            http_response_code(500);
            echo json_encode(['error' => 'Error al eliminar cliente: ' . $res_json['curl_error']]);
        } elseif (is_array($res_json) && count($res_json) > 0) {
            http_response_code(200);
            echo json_encode(['mensaje' => 'Cliente eliminado correctamente', 'data' => $res_json]);
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'No se encontró el cliente con ese email']);
        }
    } else {
        http_response_code(405);
        echo json_encode(['error' => 'Método no permitido para eliminarCliente']);
    }
    break;


    default:
        http_response_code(404);
        echo json_encode(['error' => 'Acción no encontrada']);
        break;
}
