<?php
require_once(__DIR__ . '/../config/database.php');

class Carrito {
    // Función interna para obtener id_cliente a partir del email
    private static function obtenerIdClientePorEmail($email) {
        if (empty($email)) {
            return null;
        }
        $db = new Database();

        $urlCliente = $db->getBaseUrl() . "clientes?email=eq." . rawurlencode($email);
        $chCliente = curl_init($urlCliente);
        curl_setopt($chCliente, CURLOPT_HTTPHEADER, $db->getHeaders());
        curl_setopt($chCliente, CURLOPT_RETURNTRANSFER, true);
        $respuestaCliente = curl_exec($chCliente);
        curl_close($chCliente);

        $clienteData = json_decode($respuestaCliente, true);

        if (!is_array($clienteData) || count($clienteData) === 0) {
            return null;
        }

        return intval($clienteData[0]['id']);
    }

    // Agregar al carrito
    public static function agregarAlCarrito($datos) {
        if (empty($datos['email']) || empty($datos['id_curso'])) {
            return json_encode(['error' => 'Faltan datos: email o id_curso']);
        }

        $id_cliente = self::obtenerIdClientePorEmail($datos['email']);
        if (!$id_cliente) {
            return json_encode(['error' => 'Cliente no encontrado con ese email']);
        }

        $db = new Database();

        // Comprobar si ya existe ese curso en el carrito
        $url = $db->getBaseUrl() . "carrito?and=(id_cliente.eq.{$id_cliente},id_curso.eq.{$datos['id_curso']})";
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $db->getHeaders());
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        curl_close($ch);

        $resultado = json_decode($response, true);

        if (count($resultado) > 0) {
            return json_encode(['error' => 'El curso ya está en el carrito']);
        }

        // Insertar curso al carrito
        $payload = [
            'id_cliente' => $id_cliente,
            'id_curso' => intval($datos['id_curso'])
        ];

        $jsonPayload = json_encode($payload);

        $urlInsert = $db->getBaseUrl() . "carrito";
        $headers = array_merge(
            array_filter($db->getHeaders(), fn($h) => stripos($h, 'Content-Type:') === false && stripos($h, 'Prefer:') === false),
            ['Content-Type: application/json', 'Prefer: return=representation']
        );

        $chInsert = curl_init($urlInsert);
        curl_setopt($chInsert, CURLOPT_POST, true);
        curl_setopt($chInsert, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($chInsert, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($chInsert, CURLOPT_POSTFIELDS, $jsonPayload);
        $resp = curl_exec($chInsert);
        curl_close($chInsert);

        $respDecoded = json_decode($resp, true);

        if (isset($respDecoded[0])) {
            return json_encode(['mensaje' => 'Curso agregado al carrito', 'data' => $respDecoded[0]]);
        }

        return json_encode(['error' => 'No se pudo agregar al carrito']);
    }

    // Obtener carrito
    public static function obtenerCarrito($email) {
        if (empty($email)) {
            return json_encode(['error' => 'Falta email']);
        }

        $id_cliente = self::obtenerIdClientePorEmail($email);
        if (!$id_cliente) {
            return json_encode(['error' => 'Cliente no encontrado con ese email']);
        }

        $db = new Database();
        $url = $db->getBaseUrl() . "carrito?select=*,cursos(id,titulo,precio)&id_cliente=eq.{$id_cliente}";

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $db->getHeaders());
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        curl_close($ch);

        $carrito = json_decode($response, true);

        return json_encode($carrito);
    }

    // Eliminar del carrito
    public static function eliminarDelCarrito($email, $id_curso) {
        if (empty($email) || empty($id_curso)) {
            return json_encode(['error' => 'Faltan email o id_curso']);
        }

        $id_cliente = self::obtenerIdClientePorEmail($email);
        if (!$id_cliente) {
            return json_encode(['error' => 'Cliente no encontrado con ese email']);
        }

        $db = new Database();
        $url = $db->getBaseUrl() . "carrito?and=(id_cliente.eq.{$id_cliente},id_curso.eq.{$id_curso})";

        $headers = array_merge(
            array_filter($db->getHeaders(), fn($h) => stripos($h, 'Content-Type:') === false && stripos($h, 'Prefer:') === false),
            ['Prefer: return=representation']
        );

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        curl_close($ch);

        return json_encode(['mensaje' => 'Curso eliminado del carrito']);
    }

    // Vaciar carrito
    public static function vaciarCarrito($email) {
        if (empty($email)) {
            return json_encode(['error' => 'Falta email']);
        }

        $id_cliente = self::obtenerIdClientePorEmail($email);
        if (!$id_cliente) {
            return json_encode(['error' => 'Cliente no encontrado con ese email']);
        }

        $db = new Database();
        $url = $db->getBaseUrl() . "carrito?id_cliente=eq.{$id_cliente}";

        $headers = array_merge(
            array_filter($db->getHeaders(), fn($h) => stripos($h, 'Content-Type:') === false && stripos($h, 'Prefer:') === false),
            ['Prefer: return=representation']
        );

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        curl_close($ch);

        return json_encode(['mensaje' => 'Carrito vaciado']);
    }
}
?>
