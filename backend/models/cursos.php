<?php
require_once(__DIR__ . '/../config/database.php');

class Cursos {
    // Obtener todos los cursos
    public static function obtenerCursos() {
        $db = new Database();
        $url = $db->getBaseUrl() . 'cursos';

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $db->getHeaders());
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            $error_msg = curl_error($ch);
            curl_close($ch);
            return json_encode(['curl_error' => $error_msg]);
        }

        curl_close($ch);
        return $response ?: json_encode([]);
    }

    // Actualizar un curso por ID
    public static function actualizarCurso($id, $datos) {
        $db = new Database();
        $url = $db->getBaseUrl() . 'cursos?id=eq.' . $id;

        $jsonDatos = json_encode($datos);

        $headersBase = $db->getHeaders();
        $headersFiltrados = array_filter($headersBase, function ($header) {
            return stripos($header, 'Content-Type:') === false &&
                stripos($header, 'Prefer:') === false;
        });

        $headersFinales = array_merge($headersFiltrados, [
            'Content-Type: application/json',
            'Prefer: return=representation'
        ]);

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PATCH');
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headersFinales);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonDatos);

        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            $error_msg = curl_error($ch);
            curl_close($ch);
            return json_encode(['curl_error' => $error_msg]);
        }

        curl_close($ch);
        return $response ?: json_encode([]);
    }

    // Eliminar un curso por ID
    public static function eliminarCurso($id) {
        $db = new Database();
        $url = $db->getBaseUrl() . 'cursos?id=eq.' . $id;

        $headersBase = $db->getHeaders();
        $headersFiltrados = array_filter($headersBase, function ($header) {
            return stripos($header, 'Prefer:') === false;
        });

        $headersFinales = array_merge($headersFiltrados, [
            'Prefer: return=representation'
        ]);

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headersFinales);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            $error_msg = curl_error($ch);
            curl_close($ch);
            return json_encode(['curl_error' => $error_msg]);
        }

        curl_close($ch);
        return $response ?: json_encode([]);
    }

    // Crear un curso
    public static function crearCurso($datos) {
        $db = new Database();
        $url = $db->getBaseUrl() . 'cursos';

        $jsonDatos = json_encode($datos);
        error_log("POST a: $url");
        error_log("Payload: $jsonDatos");

        $headersBase = $db->getHeaders();

        // Filtra headers para evitar duplicados de Content-Type o Prefer
        $headersFiltrados = array_filter($headersBase, function ($header) {
            return stripos($header, 'Content-Type:') === false &&
                stripos($header, 'Prefer:') === false;
        });

        // Ahora sí arma los headers correctos
        $headersFinales = array_merge($headersFiltrados, [
            'Content-Type: application/json',
            'Prefer: return=representation'
        ]);

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headersFinales);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonDatos);

        $response = curl_exec($ch);
        error_log("Respuesta de Supabase POST: " . $response);

        if (curl_errno($ch)) {
            $error_msg = curl_error($ch);
            curl_close($ch);
            return json_encode(['curl_error' => $error_msg]);
        }

        curl_close($ch);
        return $response ?: json_encode([]);
    }

    // Obtener cursos por email
    public static function obtenerCursoPorEmail($email) {
        $db = new Database();
        $encodedEmail = urlencode($email);

        // Paso 1: Obtener id del cliente
        $urlCliente = $db->getBaseUrl() . "clientes?email=eq.$encodedEmail";
        $ch1 = curl_init($urlCliente);
        curl_setopt($ch1, CURLOPT_HTTPHEADER, $db->getHeaders());
        curl_setopt($ch1, CURLOPT_RETURNTRANSFER, true);
        $resCliente = curl_exec($ch1);
        curl_close($ch1);

        $clienteData = json_decode($resCliente, true);

        if (!is_array($clienteData) || count($clienteData) === 0) {
            return json_encode(['error' => 'Cliente no encontrado']);
        }

        $id_cliente = $clienteData[0]['id'];

        // Paso 2: Buscar cursos creados por este cliente
        $urlCursos = $db->getBaseUrl() . "cursos?id_creador=eq.$id_cliente";
        $ch2 = curl_init($urlCursos);
        curl_setopt($ch2, CURLOPT_HTTPHEADER, $db->getHeaders());
        curl_setopt($ch2, CURLOPT_RETURNTRANSFER, true);
        $resCursos = curl_exec($ch2);
        curl_close($ch2);

        return $resCursos ?: json_encode([]);
    }
}
?>