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

// Crear cruso
elseif ($method === 'POST') {
    if (!isset($_FILES['imagen']) || !isset($_POST['titulo']) || !isset($_POST['precio'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Faltan datos o imagen']);
        exit;
    }

    $file = $_FILES['imagen'];
    $tmpPath = $file['tmp_name'];

    if (!file_exists($tmpPath)) {
        http_response_code(500);
        echo json_encode(['error' => 'Archivo temporal no encontrado']);
        exit;
    }

    $fileName = uniqid() . '_' . basename($file['name']);
    $bucketName = 'cursos';

    // Subida con PUT
    $bucketUploadUrl = 'https://cohhdwrjbgimbnoragxf.supabase.co/storage/v1/object/' . $bucketName . '/' . $fileName;

    $db = new Database();
    $headers = $db->getHeaders();
    $authHeader = '';
    foreach ($headers as $h) {
        if (stripos($h, 'Authorization:') === 0) {
            $authHeader = $h;
            break;
        }
    }

    $ch = curl_init($bucketUploadUrl);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
    curl_setopt($ch, CURLOPT_POSTFIELDS, file_get_contents($tmpPath));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        $authHeader,
        'x-upsert: true',
        'Content-Type: ' . mime_content_type($tmpPath)
    ]);

    $uploadResponse = curl_exec($ch);

    if (curl_errno($ch)) {
        $error_msg = curl_error($ch);
        curl_close($ch);
        http_response_code(500);
        echo json_encode(['error' => 'Error al subir imagen: ' . $error_msg]);
        exit;
    }

    $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($statusCode < 200 || $statusCode >= 300) {
        http_response_code(500);
        echo json_encode(['error' => 'La imagen no se subió correctamente', 'status' => $statusCode, 'response' => $uploadResponse]);
        exit;
    }

    // Construir URL pública de la imagen
    $publicUrl = 'https://cohhdwrjbgimbnoragxf.supabase.co/storage/v1/object/public/' . $bucketName . '/' . $fileName;

    $curso = [
        'titulo' => $_POST['titulo'],
        'descripcion' => $_POST['descripcion'] ?? '',
        'instructor' => $_POST['instructor'] ?? 'Por definir',
        'imagen' => $publicUrl,
        'precio' => floatval($_POST['precio']),
        'id_creador' => intval($_POST['id_creador'] ?? 1)
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
