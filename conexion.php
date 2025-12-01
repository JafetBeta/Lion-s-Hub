<?php
// /conexion.php

// --- AJUSTA ESTOS DATOS ---
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "lions_hub_db";
// --------------------------

// Crear la conexión
$conn = new mysqli($servername, $username, $password, $dbname);

// Establecer el juego de caracteres a UTF-8
$conn->set_charset("utf8mb4");

// Verificar si la conexión falló
if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

// Iniciar la sesión en cada script que incluya este archivo
// Esto nos permite recordar si el usuario ha iniciado sesión.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Configurar el encabezado para devolver respuestas en formato JSON
header('Content-Type: application/json');

?>