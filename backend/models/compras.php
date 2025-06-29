<?php
require_once(__DIR__ . '/../config/database.php');

class Compras {
    // Realizar compra de todos los items del carrito
    public static function realizarCompra($datos) {
        // Validar campos obligatorios
        if (empty($datos['email']) || !isset($datos['total']) || !isset($datos['id_metodo_pago'])) {
            return json_encode(['error' => 'Faltan datos obligatorios: email, total o id_metodo_pago']);
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
            return json_encode(['error' => 'Cliente no encontrado con ese correo']);
        }

        $id_cliente = intval($clienteData[0]['id']);

        // Obtener items del carrito para registrar cada curso comprado
        $urlCarrito = $db->getBaseUrl() . "carrito?select=id_curso&id_cliente=eq.$id_cliente";
        $chCarrito = curl_init($urlCarrito);
        curl_setopt($chCarrito, CURLOPT_HTTPHEADER, $db->getHeaders());
        curl_setopt($chCarrito, CURLOPT_RETURNTRANSFER, true);
        $respuestaCarrito = curl_exec($chCarrito);
        curl_close($chCarrito);

        $itemsCarrito = json_decode($respuestaCarrito, true);

        if (!is_array($itemsCarrito) || count($itemsCarrito) === 0) {
            return json_encode(['error' => 'No hay items en el carrito']);
        }

        // Registrar cada curso comprado
        $comprasRealizadas = [];
        foreach ($itemsCarrito as $item) {
            $payload = [
                'id_cliente' => $id_cliente,
                'id_curso' => intval($item['id_curso']),
                'total' => floatval($datos['total'] / count($itemsCarrito)), // Dividir el total entre los cursos
                'id_metodo_pago' => intval($datos['id_metodo_pago']),
                'detalles_pago' => isset($datos['detalles_pago']) ? json_encode($datos['detalles_pago']) : null,
                'fecha_compra' => date('Y-m-d H:i:s')
            ];

            $jsonDatos = json_encode($payload);
            $urlCompra = $db->getBaseUrl() . 'compras';
            $headers = array_merge(
                array_filter($db->getHeaders(), fn($h) => stripos($h, 'Content-Type:') === false && stripos($h, 'Prefer:') === false),
                ['Content-Type: application/json', 'Prefer: return=representation']
            );

            $ch = curl_init($urlCompra);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonDatos);
            $response = curl_exec($ch);
            curl_close($ch);

            $res_json = json_decode($response, true);
            if (is_array($res_json) && isset($res_json[0])) {
                $comprasRealizadas[] = $res_json[0];
            }
        }

        if (count($comprasRealizadas) === count($itemsCarrito)) {
            return json_encode([
                'mensaje' => 'Compra realizada correctamente',
                'data' => $comprasRealizadas,
                'total_cursos' => count($comprasRealizadas),
                'total_pagado' => $datos['total']
            ]);
        } else {
            return json_encode([
                'error' => 'Error al registrar algunas compras',
                'compras_exitosas' => $comprasRealizadas
            ]);
        }
    }

    // Obtener historial de compras
    public static function obtenerCompras($email) {
        $db = new Database();
        
        // Buscar cliente por email
        $urlCliente = $db->getBaseUrl() . "clientes?email=eq." . rawurlencode($email);
        $chCliente = curl_init($urlCliente);
        curl_setopt($chCliente, CURLOPT_HTTPHEADER, $db->getHeaders());
        curl_setopt($chCliente, CURLOPT_RETURNTRANSFER, true);
        $respuestaCliente = curl_exec($chCliente);
        curl_close($chCliente);

        $clienteData = json_decode($respuestaCliente, true);

        if (!is_array($clienteData) || count($clienteData) === 0) {
            return json_encode(['error' => 'Cliente no encontrado']);
        }

        $id_cliente = intval($clienteData[0]['id']);

        // Obtener compras con información de cursos
        $urlCompras = $db->getBaseUrl() . "compras?select=*,cursos:id_curso(titulo,imagen,instructor)&id_cliente=eq.$id_cliente&order=fecha_compra.desc";
        $chCompras = curl_init($urlCompras);
        curl_setopt($chCompras, CURLOPT_HTTPHEADER, $db->getHeaders());
        curl_setopt($chCompras, CURLOPT_RETURNTRANSFER, true);
        $respuestaCompras = curl_exec($chCompras);
        curl_close($chCompras);

        return $respuestaCompras;
    }
}
?>