-- --------------------------------------------------------
-- Archivo de configuración para la base de datos del proyecto Lions Hub
-- Versión del servidor:         10.4.28-MariaDB (o similar)
-- --------------------------------------------------------

--
-- 1. Creación de la base de datos (si no existe)
--
CREATE DATABASE IF NOT EXISTS `lions_hub_db` 
CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE `lions_hub_db`;

--
-- 2. Estructura de la tabla `usuarios`
-- Almacena la información completa de los estudiantes registrados.
--
CREATE TABLE `usuarios` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `nombre` VARCHAR(255) NOT NULL,
  `email` VARCHAR(100) NOT NULL,
  `password` VARCHAR(255) NOT NULL,
  `matricula` VARCHAR(20) NOT NULL,
  `carrera` VARCHAR(100) DEFAULT NULL,
  `cuatrimestre` INT(1) DEFAULT NULL,
  `salon` VARCHAR(20) DEFAULT NULL,
  `turno` VARCHAR(20) DEFAULT NULL,
  `grupo` VARCHAR(20) DEFAULT NULL,
  `telefono` VARCHAR(20) DEFAULT NULL,
  `fecha_registro` TIMESTAMP NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  UNIQUE KEY `matricula` (`matricula`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- 3. Estructura de la tabla `compras_boletos`
-- Almacena el historial de cada transacción de boletos.
--
CREATE TABLE `compras_boletos` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `id_usuario` INT(11) NOT NULL,
  `destino` VARCHAR(50) NOT NULL,
  `cantidad` INT(11) NOT NULL,
  `total_pagado` DECIMAL(10,2) NOT NULL,
  `metodo_pago` VARCHAR(50) NOT NULL,
  `fecha_compra` TIMESTAMP NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `id_usuario` (`id_usuario`),
  CONSTRAINT `compras_boletos_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;