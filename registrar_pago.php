<?php
// admin_api/registrar_pago.php

// 1. Ruta segura
include __DIR__ . '/../conexion.php';

header('Content-Type: application/json');

define('TICKET_PRICE_MATAMOROS', 10);
define('TICKET_PRICE_VALLE_HERMOSO', 20);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $matricula = $_POST['matricula'] ?? null;
    $cantidad = (int)($_POST['cantidad'] ?? 0);
    $destino = $_POST['destino'] ?? null;
    
    if (!$matricula || $cantidad <= 0 || !$destino) {
        echo json_encode(['success' => false, 'message' => 'Datos incompletos.']);
        exit;
    }

    // Buscar ID por matrícula
    $stmt = $conn->prepare("SELECT id FROM usuarios WHERE matricula = ?");
    $stmt->bind_param("s", $matricula);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'No se encontró un estudiante con esa matrícula.']);
        exit;
    }
    $user = $result->fetch_assoc();
    $id_usuario = $user['id'];
    $stmt->close();
    
    // Calcular total
    $total_pagado = 0;
    if ($destino === 'Valle Hermoso') {
        $total_pagado = $cantidad * TICKET_PRICE_VALLE_HERMOSO;
    } else {
        $total_pagado = $cantidad * TICKET_PRICE_MATAMOROS;
    }
    
    // Insertar compra
    $metodo_pago = 'Efectivo (Presencial Admin)';
    $stmt = $conn->prepare("INSERT INTO compras_boletos (id_usuario, destino, cantidad, total_pagado, metodo_pago, fecha_compra) VALUES (?, ?, ?, ?, ?, NOW())");
    $stmt->bind_param("isids", $id_usuario, $destino, $cantidad, $total_pagado, $metodo_pago);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Pago registrado correctamente.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al registrar el pago: ' . $stmt->error]);
    }

    $stmt->close();
}
$conn->close();
?>