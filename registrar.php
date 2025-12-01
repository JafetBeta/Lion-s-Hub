<?php
// /api/registrar.php

include '../conexion.php';

// Verificamos que se envíen datos por POST
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Recibimos todos los datos del formulario
    $nombre = $_POST['registerNombre'] ?? '';
    $email = $_POST['registerEmail'] ?? '';
    $matricula = $_POST['registerMatricula'] ?? '';
    $telefono = $_POST['registerTelefono'] ?? '';
    $carrera = $_POST['registerCarrera'] ?? '';
    $cuatrimestre = $_POST['registerCuatrimestre'] ?? '';
    $grupo = $_POST['registerGrupo'] ?? '';
    $turno = $_POST['registerTurno'] ?? '';
    $password = $_POST['registerPassword'] ?? '';
    $confirmPassword = $_POST['confirmPassword'] ?? '';

    // --- VALIDACIONES ---
    $campos = [$nombre, $email, $matricula, $telefono, $carrera, $cuatrimestre, $grupo, $turno, $password, $confirmPassword];
    if (in_array('', $campos, true)) {
        echo json_encode(['success' => false, 'message' => 'Todos los campos son obligatorios.']);
        exit;
    }

    // Validación específica para el correo
    if (!str_ends_with(strtolower($email), '@utmatamoros.edu.mx')) {
        echo json_encode(['success' => false, 'message' => 'El correo debe ser del dominio @utmatamoros.edu.mx']);
        exit;
    }

    if ($password !== $confirmPassword) {
        echo json_encode(['success' => false, 'message' => 'Las contraseñas no coinciden.']);
        exit;
    }

    // Verificar si el correo o la matrícula ya existen
    $stmt = $conn->prepare("SELECT id FROM usuarios WHERE email = ? OR matricula = ?");
    $stmt->bind_param("ss", $email, $matricula);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        echo json_encode(['success' => false, 'message' => 'El correo electrónico o la matrícula ya están registrados.']);
        $stmt->close();
        exit;
    }
    $stmt->close();

    // Encriptar la contraseña
    $passwordHashed = password_hash($password, PASSWORD_DEFAULT);
    
    // --- INSERCIÓN EN LA BASE DE DATOS ---
    $stmt = $conn->prepare("INSERT INTO usuarios (nombre, email, matricula, telefono, carrera, cuatrimestre, grupo, turno, password) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    // s = string, i = integer
    $stmt->bind_param("sssssisss", $nombre, $email, $matricula, $telefono, $carrera, $cuatrimestre, $grupo, $turno, $passwordHashed);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => '¡Registro exitoso! Ahora puedes iniciar sesión.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al registrar el usuario: ' . $stmt->error]);
    }

    $stmt->close();
}

$conn->close();
?>