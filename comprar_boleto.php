<?php
// /api/comprar_boleto.php
include '../conexion.php'; // Asegúrate que la ruta a conexion sea correcta

define('PRECIO_MATAMOROS', 10);
define('PRECIO_VALLE_HERMOSO', 20);

// Verificar sesión
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'No has iniciado sesión.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_usuario = $_SESSION['user_id'];
    
    // Leemos los datos enviados por JSON (necesario para PayPal) o POST normal
    $input = json_decode(file_get_contents('php://input'), true);
    // Si no es JSON, intentamos leer POST normal
    if (!$input) {
        $input = $_POST;
    }

    $destino = $input['destino'] ?? '';
    $cantidad = (int)($input['cantidad'] ?? 0);
    $metodo_pago = $input['pago'] ?? ''; // 'physical' o 'paypal'
    $paypal_order_id = $input['paypal_order_id'] ?? null; // ID de transacción de PayPal

    // 1. VALIDACIÓN BÁSICA
    if (empty($destino) || $cantidad <= 0 || empty($metodo_pago)) {
        echo json_encode(['success' => false, 'message' => 'Datos incompletos.']);
        exit;
    }

    // 2. CORRECCIÓN DE SEGURIDAD: CALCULAR PRECIO EN EL SERVIDOR
    // Ignoramos cualquier "total" que envíe el usuario.
    $precio_unitario = 0;
    if (strpos($destino, 'Valle Hermoso') !== false) {
        $precio_unitario = PRECIO_VALLE_HERMOSO;
    } else {
        // Asumimos que cualquier otra ruta es Matamoros ($10)
        // Puedes agregar validación estricta de nombres de rutas aquí si deseas
        $precio_unitario = PRECIO_MATAMOROS;
    }

    $total_a_pagar = $cantidad * $precio_unitario;

    // 3. REGISTRAR EN BASE DE DATOS
    // Guardamos el metodo de pago de forma legible
    $metodo_texto = ($metodo_pago === 'paypal') ? "PayPal (ID: $paypal_order_id)" : 'Efectivo (Pendiente)';

    $stmt = $conn->prepare("INSERT INTO compras_boletos (id_usuario, destino, cantidad, total_pagado, metodo_pago) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("isids", $id_usuario, $destino, $cantidad, $total_a_pagar, $metodo_texto);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Compra registrada correctamente.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error en base de datos.']);
    }
    $stmt->close();
}
$conn->close();
?>