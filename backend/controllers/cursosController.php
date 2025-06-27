<?php
require_once(__DIR__ . '/../models/cursos.php');

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];

// Obtener cursos
if ($method === 'GET') {
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

// Actualizar curso
elseif ($method === 'PATCH') {
    $input = json_decode(file_get_contents("php://input"), true);

    if (!isset($input['id'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Se requiere un ID del curso para actualizar']);
        exit;
    }

    $id = $input['id'];
    unset($input['id']);

    $resultado = Cursos::actualizarCurso($id, $input);
    $res_json = json_decode($resultado, true);

    if (is_array($res_json) && count($res_json) > 0) {
        http_response_code(201);
        echo json_encode(['mensaje' => 'Curso actualizado correctamente', 'data' => $res_json]);
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'No se actualizó el curso']);
    }
}

// Eliminar curso
elseif ($method === 'DELETE') {
    $input = json_decode(file_get_contents("php://input"), true);

    if (!isset($input['id'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Se requiere un ID del curso para eliminar']);
        exit;
    }

    $id = $input['id'];
    $resultado = Cursos::eliminarCurso($id);
    $res_json = json_decode($resultado, true);

    if (isset($res_json['curl_error'])) {
        http_response_code(500);
        echo json_encode(['error' => 'Error al eliminar el curso: ' . $res_json['curl_error']]);
    } elseif (is_array($res_json) && count($res_json) > 0) {
        http_response_code(200);
        echo json_encode(['mensaje' => 'Curso eliminado correctamente', 'data' => $res_json]);
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'No se encontró el curso para eliminar']);
    }
}

// Crear curso
elseif ($method === 'POST') {
    $input = json_decode(file_get_contents("php://input"), true);

    // Validación de campos obligatorios
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

    // Preparar curso
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
}

// Obtener cursos por email
elseif ($method === 'GET') {
    // Verificar si viene ?email= en la URL
    if (isset($_GET['email'])) {
        $email = $_GET['email'];
        $resultado = Cursos::obtenerCursoPorEmail($email);
    } else {
        $resultado = Cursos::obtenerCursos();
    }

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
}
?>
