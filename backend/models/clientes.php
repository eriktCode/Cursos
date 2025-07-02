<?php
require_once(__DIR__ . '/../config/database.php');

class Clientes {
    // Obtener todos los clientes
    public static function obtenerClientes() {
        $db = new Database();
        $url = $db->getBaseUrl() . 'clientes';

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

    // Eliminar cliente por ID
    public static function eliminarCliente($id) {
        $db = new Database();
        $url = $db->getBaseUrl() . 'clientes?id=eq.' . intval($id);

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

    // Login 
    public static function login($email, $llave_secreta) {
        $db = new Database();
        
        $url = $db->getBaseUrl() . 'clientes?email=eq.' . urlencode($email) . '&llave_secreta=eq.' . urlencode($llave_secreta) . '&select=*';

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

        // Devuelve array con datos o vacío
        return $response ?: json_encode([]);
    }

    // Crear nuevo cliente
    public static function crearCliente($datosCliente) {
        $db = new Database();
        $url = $db->getBaseUrl() . 'clientes';

        // Validación básica
        if (!isset($datosCliente['email'])) {
            return json_encode(['error' => 'El email es requerido']);
        }

        // Generar id_cliente encriptado (SHA-256)
        $id_cliente = hash('sha256', $datosCliente['email'] . microtime() . random_int(1000, 9999));

        // Encriptar llave_secreta (usando password_hash)
        $llave_secreta_encriptada = password_hash(
            $datosCliente['llave_secreta'], 
            PASSWORD_BCRYPT,
            ['cost' => 12]
        );

        // Preparar datos para insertar
        $datosInsertar = [
            'nombre' => $datosCliente['nombre'],
            'apellido' => $datosCliente['apellido'],
            'email' => $datosCliente['email'],
            'id_cliente' => $id_cliente,
            'llave_secreta' => $llave_secreta_encriptada
        ];

        // Configurar headers (sin duplicados)
        $headersBase = $db->getHeaders();
        $headersFiltrados = array_filter($headersBase, function($header) {
            return stripos($header, 'Content-Type:') === false;
        });

        $headersFinales = array_merge($headersFiltrados, [
            'Content-Type: application/json',
            'Prefer: return=representation'
        ]);

        // Realizar petición
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headersFinales);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($datosInsertar));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        // Manejar respuesta
        if ($httpCode >= 400) {
            return json_encode([
                'error' => 'Error al crear cliente',
                'http_code' => $httpCode,
                'response' => json_decode($response, true)
            ]);
        }

        return $response;
    }
}
?>