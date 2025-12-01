<?php
// api/perfil.php

// 1. Configuración inicial
session_start();
header('Content-Type: application/json'); // Forzamos respuesta JSON

// 2. Conexión segura (usando __DIR__ para evitar errores de ruta)
include __DIR__ . '/../conexion.php';

// 3. Verificar sesión
if (isset($_SESSION['user_id'])) {
    $userId = $_SESSION['user_id'];

    // --- CORRECCIÓN IMPORTANTE: Cambiamos 'salon' por 'grupo' ---
    // Antes: ... cuatrimestre, salon, turno ...
    // Ahora: ... cuatrimestre, grupo, turno ...
    $sql = "SELECT nombre, email, matricula, carrera, cuatrimestre, grupo, turno FROM usuarios WHERE id = ?";
    
    // Preparamos la consulta con manejo de errores
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($user = $result->fetch_assoc()) {
            echo json_encode(['success' => true, 'data' => $user]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Usuario no encontrado.']);
        }
        $stmt->close();
    } else {
        // Si hay error en la SQL (ej. columna no existe), lo reportamos en JSON
        echo json_encode(['success' => false, 'message' => 'Error interno de base de datos: ' . $conn->error]);
    }

} else {
    echo json_encode(['success' => false, 'message' => 'No has iniciado sesión.']);
}

$conn->close();
?>