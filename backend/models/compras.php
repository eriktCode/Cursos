<?php
require_once(__DIR__ . '/../config/database.php');

class Compras {
    // Realizar compra buscando el id_cliente por email
    public static function realizarCompra($datos) {
        // Validar campos obligatorios
        if (
            empty($datos['email']) ||
            !isset($datos['total']) ||
            !isset($datos['id_metodo_pago'])
        ) {
            return json_encode([
                'error' => 'Faltan datos obligatorios: email, total o id_metodo_pago'
            ]);
        }

        $db = new Database();

        // Buscar cliente por email
        $urlCliente = $db->getBaseUrl() . "clientes?email=eq." . rawurlencode($datos['email']);

        $chCliente = curl_init($urlCliente);
        curl_setopt($chCliente, CURLOPT_HTTPHEADER, $db->getHeaders());
        curl_setopt($chCliente, CURLOPT_RETURNTRANSFER, true);
        $respuestaCliente = curl_exec($chCliente);
        curl_close($chCliente);

        $clienteData = json_decode($respuestaCliente, true);

        if (!is_array($clienteData) || count($clienteData) === 0) {
            return json_encode([
                'error' => 'Cliente no encontrado con ese correo'
            ]);
        }

        $id_cliente = intval($clienteData[0]['id']);

        // Preparar datos para insertar compra
        $payload = [
            'id_cliente' => $id_cliente,
            'id_curso' => intval($datos['id_curso']),
            'total' => floatval($datos['total']),
            'id_metodo_pago' => intval($datos['id_metodo_pago']),
            'detalles_pago' => isset($datos['detalles_pago']) ? $datos['detalles_pago'] : null
        ];


        $jsonDatos = json_encode($payload);

        $urlCompra = $db->getBaseUrl() . 'compras';
        $headersBase = $db->getHeaders();
        $headersFiltrados = array_filter($headersBase, function ($h) {
            return stripos($h, 'Content-Type:') === false &&
                stripos($h, 'Prefer:') === false;
        });

        $headers = array_merge($headersFiltrados, [
            'Content-Type: application/json',
            'Prefer: return=representation'
        ]);

        $ch = curl_init($urlCompra);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonDatos);

        $response = curl_exec($ch);
        curl_close($ch);

        $res_json = json_decode($response, true);

        if (is_array($res_json) && isset($res_json[0])) {
            return json_encode([
                'mensaje' => 'Compra realizada correctamente',
                'data' => $res_json[0]
            ]);
        } else {
            return json_encode([
                'error' => 'Error al realizar la compra',
                'debug' => $res_json
            ]);
        }
    }
}
?>
