<?php
// admin_api/obtener_datos.php

// 1. Usar ruta segura para la conexión
include __DIR__ . '/../conexion.php';

header('Content-Type: application/json');

// Definimos precios
define('TICKET_PRICE_MATAMOROS', 10);
define('TICKET_PRICE_VALLE_HERMOSO', 20);

try {
    // 2. Consulta corregida: Usamos 'grupo' directamente (ya no existe 'salon')
    $sql_usuarios = "SELECT id, nombre, matricula, carrera, grupo, telefono, email FROM usuarios";
    
    if (!$result_usuarios = $conn->query($sql_usuarios)) {
        throw new Exception("Error en consulta usuarios: " . $conn->error);
    }

    $estudiantes = [];
    while($row = $result_usuarios->fetch_assoc()) {
        $row['boletos'] = []; 
        $estudiantes[$row['id']] = $row;
    }

    // 3. Obtener compras
    $sql_boletos = "SELECT id as boleto_id, id_usuario, cantidad, destino, fecha_compra, metodo_pago FROM compras_boletos";
    
    if (!$result_boletos = $conn->query($sql_boletos)) {
        throw new Exception("Error en consulta boletos: " . $conn->error);
    }

    while($row = $result_boletos->fetch_assoc()) {
        if (isset($estudiantes[$row['id_usuario']])) {
            $estudiantes[$row['id_usuario']]['boletos'][] = [
                'id' => $row['boleto_id'],
                'cantidad' => (int)$row['cantidad'],
                'destino' => $row['destino'],
                'estado' => 'Pagado',
                'fechaHoraCompra' => $row['fecha_compra'],
                'metodoPago' => $row['metodo_pago']
            ];
        }
    }

    // 4. Procesar datos
    $datos_procesados = [];
    foreach ($estudiantes as $estudiante) {
        $paidTickets = $estudiante['boletos'];
        $totalBoletosCount = array_reduce($paidTickets, fn($sum, $b) => $sum + $b['cantidad'], 0);

        $totalRevenue = 0;
        foreach ($paidTickets as $boleto) {
            $ingreso = 0;
            // Detección simple de precio basada en destino
            if (strpos($boleto['destino'], 'Valle Hermoso') !== false) {
                $ingreso = $boleto['cantidad'] * TICKET_PRICE_VALLE_HERMOSO;
            } else {
                $ingreso = $boleto['cantidad'] * TICKET_PRICE_MATAMOROS;
            }
            $totalRevenue += $ingreso;
        }

        $estadoGeneral = count($paidTickets) > 0 ? 'Pagado' : 'Pendiente';
        
        $ultimaFechaPago = '-';
        if (count($paidTickets) > 0) {
            usort($paidTickets, fn($a, $b) => strtotime($b['fechaHoraCompra']) - strtotime($a['fechaHoraCompra']));
            $ultimaFechaPago = date('Y-m-d', strtotime($paidTickets[0]['fechaHoraCompra']));
        }
        
        $datos_procesados[] = array_merge($estudiante, [
            'boletosPagadosCount' => $totalBoletosCount,
            'totalRevenue' => $totalRevenue,
            'estadoGeneral' => $estadoGeneral,
            'ultimaFechaPago' => $ultimaFechaPago,
        ]);
    }
    
    echo json_encode(['success' => true, 'data' => array_values($datos_procesados)]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error en el servidor: ' . $e->getMessage()]);
}

$conn->close();
?>