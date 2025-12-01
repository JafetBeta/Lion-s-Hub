<?php
// /api/admin_auth.php
session_start();

// --- CONFIGURACIÓN DE ACCESO ---
// CAMBIA ESTO POR TU CONTRASEÑA SEGURA
$ADMIN_USER = 'admin';
$ADMIN_PASS = 'Lions2025secure!'; 
// ------------------------------

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = $_POST['username'] ?? '';
    $pass = $_POST['password'] ?? '';

    if ($user === $ADMIN_USER && $pass === $ADMIN_PASS) {
        $_SESSION['admin_logged_in'] = true;
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Credenciales incorrectas']);
    }
}
?>