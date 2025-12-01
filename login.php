<?php
// /api/login.php

session_start(); // <-- AÑADE ESTA LÍNEA AL PRINCIPIO DE TODO

include '../conexion.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['loginEmail'] ?? '';
    $password = $_POST['loginPassword'] ?? '';

    if (empty($email) || empty($password)) {
        echo json_encode(['success' => false, 'message' => 'Correo y contraseña son obligatorios.']);
        exit;
    }

    // Buscar al usuario por correo
    $stmt = $conn->prepare("SELECT id, password FROM usuarios WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        
        // Verificar la contraseña encriptada
        if (password_verify($password, $user['password'])) {
            // Contraseña correcta: Iniciar sesión
            $_SESSION['user_id'] = $user['id'];
            echo json_encode(['success' => true]);
        } else {
            // Contraseña incorrecta
            echo json_encode(['success' => false, 'message' => 'Correo o contraseña incorrectos.']);
        }
    } else {
        // Usuario no encontrado
        echo json_encode(['success' => false, 'message' => 'Correo o contraseña incorrectos.']);
    }
    $stmt->close();
}

$conn->close();
?>