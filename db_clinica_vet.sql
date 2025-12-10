-- 1. CONFIGURACIÓN INICIAL
SET FOREIGN_KEY_CHECKS = 0;
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

-- 2. CREACIÓN DE LA BASE DE DATOS (Si no existe)
CREATE DATABASE IF NOT EXISTS `clinica_veterinaria` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `clinica_veterinaria`;

-- --------------------------------------------------------
-- 3. ELIMINAR TABLAS ANTIGUAS (Para instalación limpia)
-- --------------------------------------------------------
DROP TABLE IF EXISTS `citas`;
DROP TABLE IF EXISTS `consultas`;
DROP TABLE IF EXISTS `mascotas`;
DROP TABLE IF EXISTS `razas`;
DROP TABLE IF EXISTS `especies`;
DROP TABLE IF EXISTS `propietarios`;
DROP TABLE IF EXISTS `usuarios`;

-- --------------------------------------------------------
-- 4. ESTRUCTURA DE TABLAS
-- --------------------------------------------------------

-- Tabla: usuarios
CREATE TABLE `usuarios` (
  `id_usuario` int(11) NOT NULL AUTO_INCREMENT,
  `nombre_completo` varchar(100) NOT NULL,
  `correo` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `rol` enum('admin','veterinario') NOT NULL,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_usuario`),
  UNIQUE KEY `correo` (`correo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabla: propietarios
CREATE TABLE `propietarios` (
  `id_propietario` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) NOT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `direccion` text DEFAULT NULL,
  `correo` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id_propietario`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabla: especies (Necesaria para los selects)
CREATE TABLE `especies` (
  `id_especie` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(50) NOT NULL,
  PRIMARY KEY (`id_especie`),
  UNIQUE KEY `nombre` (`nombre`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabla: razas (Necesaria para los selects)
CREATE TABLE `razas` (
  `id_raza` int(11) NOT NULL AUTO_INCREMENT,
  `id_especie` int(11) NOT NULL,
  `nombre` varchar(80) NOT NULL,
  PRIMARY KEY (`id_raza`),
  KEY `id_especie` (`id_especie`),
  CONSTRAINT `razas_ibfk_1` FOREIGN KEY (`id_especie`) REFERENCES `especies` (`id_especie`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabla: mascotas
CREATE TABLE `mascotas` (
  `id_mascota` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(50) NOT NULL,
  `especie` varchar(50) NOT NULL,
  `raza` varchar(50) DEFAULT NULL,
  `sexo` enum('Macho','Hembra') NOT NULL,
  `color` varchar(30) DEFAULT NULL,
  `fecha_nacimiento` date DEFAULT NULL,
  `id_propietario` int(11) NOT NULL,
  PRIMARY KEY (`id_mascota`),
  KEY `id_propietario` (`id_propietario`),
  CONSTRAINT `mascotas_ibfk_1` FOREIGN KEY (`id_propietario`) REFERENCES `propietarios` (`id_propietario`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabla: consultas
CREATE TABLE `consultas` (
  `id_consulta` int(11) NOT NULL AUTO_INCREMENT,
  `id_mascota` int(11) NOT NULL,
  `id_veterinario` int(11) NOT NULL,
  `fecha_consulta` timestamp NOT NULL DEFAULT current_timestamp(),
  `peso_kg` decimal(5,2) DEFAULT NULL,
  `tamano` varchar(50) DEFAULT NULL,
  `observacion_clinica` text DEFAULT NULL,
  `observacion_sistema` text DEFAULT NULL,
  `conclusion_diagnostico` text DEFAULT NULL,
  `receta` text DEFAULT NULL,
  `costo_servicio` decimal(10,2) NOT NULL,
  PRIMARY KEY (`id_consulta`),
  KEY `id_mascota` (`id_mascota`),
  KEY `id_veterinario` (`id_veterinario`),
  CONSTRAINT `consultas_ibfk_1` FOREIGN KEY (`id_mascota`) REFERENCES `mascotas` (`id_mascota`) ON DELETE CASCADE,
  CONSTRAINT `consultas_ibfk_2` FOREIGN KEY (`id_veterinario`) REFERENCES `usuarios` (`id_usuario`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabla: citas
CREATE TABLE `citas` (
  `id_cita` int(11) NOT NULL AUTO_INCREMENT,
  `id_mascota` int(11) NOT NULL,
  `id_veterinario` int(11) NOT NULL,
  `fecha_cita` date NOT NULL,
  `hora_cita` time NOT NULL,
  `motivo` varchar(255) NOT NULL,
  `estado` enum('Pendiente','Realizada','Cancelada') NOT NULL DEFAULT 'Pendiente',
  PRIMARY KEY (`id_cita`),
  KEY `id_mascota` (`id_mascota`),
  KEY `id_veterinario` (`id_veterinario`),
  CONSTRAINT `citas_ibfk_1` FOREIGN KEY (`id_mascota`) REFERENCES `mascotas` (`id_mascota`) ON DELETE CASCADE,
  CONSTRAINT `citas_ibfk_2` FOREIGN KEY (`id_veterinario`) REFERENCES `usuarios` (`id_usuario`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- 5. DATOS INICIALES (SOLO ESPECIES Y RAZAS)
-- --------------------------------------------------------

-- Especies Básicas
INSERT INTO `especies` (`id_especie`, `nombre`) VALUES
(1, 'Canino'),
(2, 'Felino'),
(3, 'Bovino'),
(4, 'Equino'),
(5, 'Porcino');

-- Razas Básicas (Limpias, sin números)
INSERT INTO `razas` (`id_raza`, `id_especie`, `nombre`) VALUES
-- Perros
(1, 1, 'Labrador Retriever'),
(2, 1, 'Pastor Alemán'),
(3, 1, 'Golden Retriever'),
(4, 1, 'Poodle'),
(5, 1, 'Pitbull'),
(6, 1, 'Bulldog Francés'),
(7, 1, 'Beagle'),
(8, 1, 'Rottweiler'),
(9, 1, 'Chihuahua'),
(10, 1, 'Schnauzer'),
-- Gatos
(11, 2, 'Persa'),
(12, 2, 'Siamés'),
(13, 2, 'Maine Coon'),
(14, 2, 'Bengalí'),
(15, 2, 'Esfinge'),
-- Vacas
(21, 3, 'Holstein'),
(22, 3, 'Jersey'),
(23, 3, 'Brahman'),
-- Caballos
(31, 4, 'Árabe'),
(32, 4, 'Pura Sangre'),
(33, 4, 'Cuarto de Milla'),
-- Cerdos
(41, 5, 'Landrace'),
(42, 5, 'Duroc');

-- 6. FINALIZAR
SET FOREIGN_KEY_CHECKS = 1;
COMMIT;