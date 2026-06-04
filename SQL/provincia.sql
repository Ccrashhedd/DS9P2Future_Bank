-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: localhost:3306
-- Tiempo de generación: 09-05-2024 a las 23:10:06
-- Versión del servidor: 10.6.16-MariaDB-cll-lve
-- Versión de PHP: 8.1.25

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `escuelae_estudiantes`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `provincia`
--

CREATE TABLE `provincia` (
  `codigo_provincia` varchar(2) NOT NULL PRIMARY KEY,
  `nombre_provincia` varchar(50) NOT NULL
) ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `provincia`
--

INSERT INTO `provincia` (`codigo_provincia`, `nombre_provincia`) VALUES
('01', 'BOCAS DEL TORO'),
('02', 'COCLE'),
('03', 'COLON'),
('04', 'CHIRIQUI'),
('05', 'DARIEN'),
('06', 'HERRERA'),
('07', 'LOS SANTOS'),
('08', 'PANAMA'),
('09', 'VERAGUAS'),
('10', 'COMARCA GUNAYALA'),
('11', 'COMARCA EMBERA WOUNAAN'),
('12', 'COMARCA NGABE-BUGLE'),
('13', 'PANAMA OESTE');

--
-- Índices para tablas volcadas
--
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
