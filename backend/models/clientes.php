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
}
?>
