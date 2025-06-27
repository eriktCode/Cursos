<?php
require_once(__DIR__ . '/../models/cursos.php');

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];

// Obtener la ruta como /obtenerCursos, /crearCurso, etc.
$path = isset($_SERVER['PATH_INFO']) ? trim($_SERVER['PATH_INFO'], '/') : '';

// Arreglo para multipart/form-data + PATH_INFO
if ($method === 'POST' && $path === 'crearCurso' && empty($_FILES) && strpos($_SERVER['CONTENT_TYPE'] ?? '', 'multipart/form-data') !== false) {
    parse_str(file_get_contents('php://input'), $_POST);
}

// --------------------
// RUTAS CON GET
// --------------------
if ($method === 'GET' && $path === 'obtenerCursos') {
    $resultado = Cursos::obtenerCursos();
    $res_json = json_decode($resultado, true);

    if (isset($res_json['curl_error'])) {
        http_response_code(500);
        echo json_encode(['error' => 'Error de conexión: ' . $res_json['curl_error']]);
    } elseif ($res_json && $resultado !== '[]') {
        http_response_code(200);
        echo $resultado;
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'No se encontraron cursos']);
    }

}

// --------------------
// NUEVA RUTA POST para buscar cursos por email enviado en JSON
// --------------------
elseif ($method === 'POST' && $path === 'obtenerCursosPorEmail') {
    $input = json_decode(file_get_contents("php://input"), true);

    if (!isset($input['email'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Falta el campo email']);
        exit;
    }

    $resultado = Cursos::obtenerCursoPorEmail($input['email']);
    $res_json = json_decode($resultado, true);

    if (isset($res_json['error'])) {
        http_response_code(404);
        echo json_encode(['error' => $res_json['error']]);
    } elseif ($res_json && $resultado !== '[]') {
        http_response_code(200);
        echo $resultado;
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'No se encontraron cursos']);
    }

// --------------------
// RUTA CON POST para crear curso
// --------------------
} elseif ($method === 'POST' && $path === 'crearCurso') {
    $input = json_decode(file_get_contents("php://input"), true);

    if (
        !isset($input['titulo']) ||
        !isset($input['imagen']) ||
        !isset($input['precio']) ||
        !isset($input['id_creador'])
    ) {
        http_response_code(400);
        echo json_encode(['error' => 'Faltan datos obligatorios: titulo, imagen, precio o id_creador']);
        exit;
    }

    $curso = [
        'titulo' => $input['titulo'],
        'descripcion' => $input['descripcion'] ?? '',
        'instructor' => $input['instructor'] ?? 'Por definir',
        'imagen' => $input['imagen'],
        'precio' => floatval($input['precio']),
        'id_creador' => intval($input['id_creador'])
    ];

    $resultado = Cursos::crearCurso($curso);
    $res_json = json_decode($resultado, true);

    if (is_array($res_json) && isset($res_json[0])) {
        http_response_code(201);
        echo json_encode(['mensaje' => 'Curso creado correctamente', 'data' => $res_json[0]]);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Error al crear el curso', 'debug' => $res_json]);
    }

// --------------------
// RUTA CON PATCH
// --------------------
} elseif ($method === 'PATCH' && $path === 'actualizarCurso') {
    $input = json_decode(file_get_contents("php://input"), true);
    if (!isset($input['id'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Se requiere un ID']);
        exit;
    }

    $id = $input['id'];
    unset($input['id']);

    $resultado = Cursos::actualizarCurso($id, $input);
    $res_json = json_decode($resultado, true);

    if (is_array($res_json) && count($res_json) > 0) {
        http_response_code(201);
        echo json_encode(['mensaje' => 'Curso actualizado', 'data' => $res_json]);
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'No se actualizó el curso']);
    }

// --------------------
// RUTA CON DELETE
// --------------------
} elseif ($method === 'DELETE' && $path === 'eliminarCurso') {
    $input = json_decode(file_get_contents("php://input"), true);
    if (!isset($input['id'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Se requiere un ID']);
        exit;
    }

    $resultado = Cursos::eliminarCurso($input['id']);
    $res_json = json_decode($resultado, true);

    if (isset($res_json['curl_error'])) {
        http_response_code(500);
        echo json_encode(['error' => 'Error al eliminar: ' . $res_json['curl_error']]);
    } elseif (is_array($res_json) && count($res_json) > 0) {
        http_response_code(200);
        echo json_encode(['mensaje' => 'Curso eliminado', 'data' => $res_json]);
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'No se encontró el curso']);
    }

} else {
    http_response_code(404);
    echo json_encode(['error' => 'Ruta o método no válido']);
}
