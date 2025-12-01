<?php
// /api/logout.php

// Reanudamos la sesión existente
session_start();

// Destruimos todas las variables de sesión
$_SESSION = array();

// Finalmente, destruimos la sesión
session_destroy();

// Enviamos una respuesta de éxito
header('Content-Type: application/json');
echo json_encode(['success' => true, 'message' => 'Sesión cerrada correctamente.']);
?>