-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 16-06-2026 a las 08:01:56
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `p2gestiondocumentos`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `documento_postulante`
--

CREATE TABLE `documento_postulante` (
  `idDocumentoPostulante` int(11) NOT NULL,
  `idGradoEst` int(11) NOT NULL,
  `idPostulante` bigint(20) NOT NULL,
  `codigo_provincia` varchar(2) NOT NULL,
  `titulo` varchar(100) NOT NULL,
  `institucion` bigint(20) NOT NULL,
  `otraInstitucion` tinyint(1) DEFAULT NULL,
  `nombreOtraInstitucion` varchar(250) DEFAULT NULL,
  `fechaInicio` date NOT NULL,
  `fechaFinalizacion` date NOT NULL,
  `fechaEmision` date NOT NULL,
  `totalHoras` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `gradoacademico_documento`
--

CREATE TABLE `gradoacademico_documento` (
  `idGradoEst` int(11) NOT NULL,
  `nombreGradoEst` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `gradoacademico_documento`
--

INSERT INTO `gradoacademico_documento` (`idGradoEst`, `nombreGradoEst`) VALUES
(1, 'Certificado'),
(2, 'Curso'),
(4, 'Diploma'),
(9, 'Doctorado'),
(6, 'Licenciatura'),
(8, 'Maestria'),
(7, 'Postgrado'),
(3, 'Seminario'),
(5, 'Tecnico');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `instituciones`
--

CREATE TABLE `instituciones` (
  `idInstitucion` bigint(20) NOT NULL,
  `nombreInstitucion` varchar(250) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `instituciones`
--

INSERT INTO `instituciones` (`idInstitucion`, `nombreInstitucion`) VALUES
(20, 'ADEN University'),
(26, 'Centro de Educación Superior Academia de Formación de Bomberos de Panamá'),
(27, 'Centro de Educación Superior Mundial de Capacitación'),
(70, 'Centro de Enseñanza Superior Panamá Pacífico'),
(96, 'Centro de Estudio Superior de Panamá'),
(67, 'Centro de Estudios Regionales de Panamá'),
(89, 'Centro de Estudios Superiores de Arte y Folklore'),
(72, 'Centro de Estudios Superiores de Arte y Folklore, Changuinola'),
(90, 'Centro de Estudios Superiores de Artesanía'),
(52, 'Centro de Estudios Superiores en Seguridad y Ciencias Forenses'),
(111, 'Centro de Estudios Superiores Ingenium'),
(98, 'Centro Nacional Cooperativo de Formación y Educación Superior'),
(80, 'Centro Superior Cultural & Turismo'),
(91, 'Centro Técnico de Estudios Superiores'),
(23, 'Centro Técnico de Estudios Superiores-San Miguelito'),
(30, 'Centro Técnico de Estudios Superiores-sede Bella Vista Provincia de Panamá'),
(32, 'Centro Tecnológico de Panamá'),
(13, 'Columbus University'),
(93, 'Escuela Nacional de Folklore'),
(15, 'Florida State University - Panamá'),
(110, 'Institute Superior BRIDGE COMMUNITY COLLEGE'),
(31, 'Instituto Bancario Internacional'),
(78, 'Instituto de Educación Cooperativa'),
(83, 'Instituto de Educación Superior Nueva Luz'),
(29, 'Instituto de Enseñanza Superior Monte Horeb'),
(109, 'Instituto de Enseñanza Superior OTEIMA'),
(34, 'Instituto Internacional Superior de Comercio y Educación'),
(37, 'Instituto Nacional de Capacitación Profesional'),
(101, 'Instituto Padagógico Superior Juan Demóstenes Arosemena'),
(108, 'Instituto Politécnico de Azuero'),
(49, 'Instituto Superior Académico de Panamá'),
(75, 'Instituto Superior Aeronaval, Teniente de Fragata Manuel Castillo'),
(60, 'Instituto Superior American Christian School'),
(57, 'Instituto Superior Antequera'),
(69, 'Instituto Superior Bellas Luces'),
(59, 'Instituto Superior Benjamín Rosales Pareja'),
(38, 'Instituto Superior Bíblico de las Asambleas de Dios'),
(88, 'Instituto Superior Bilingüe Culinario de Azuero'),
(92, 'Instituto Superior Bilingüe de Centroamérica'),
(87, 'Instituto Superior C&C Technologies'),
(47, 'Instituto Superior Canadian Technical Institute'),
(84, 'Instituto Superior Centro de Líderes'),
(40, 'Instituto Superior de Adiestramiento y Soporte Técnico All Computer'),
(68, 'Instituto Superior de Administración, Investigación y Tecnología'),
(81, 'Instituto Superior de Alta Cocina'),
(45, 'Instituto Superior de Bellas Artes'),
(35, 'Instituto Superior de Ciencias y Tecnología'),
(24, 'Instituto Superior de Ciencias y Tecnología Aeronáuticas'),
(77, 'Instituto Superior de Competencias'),
(100, 'Instituto Superior de Educación Superior Nueva Luz'),
(95, 'Instituto Superior de Educación y Formación Profesional'),
(61, 'Instituto Superior de Estética y Belleza APEC, S.A.'),
(74, 'Instituto Superior de Estudios Computarizados'),
(58, 'Instituto Superior de Formación Integral en Seguros'),
(25, 'Instituto Superior de Formación Profesional Aeronáutica'),
(39, 'Instituto Superior de Ingeniería'),
(104, 'Instituto Superior de Investigaciones Criminales y Ciencias Forenses'),
(51, 'Instituto Superior de la Judicatura de Panamá Doctor César Augusto Quintero Correa'),
(66, 'Instituto Superior de las Américas S.A.'),
(107, 'Instituto Superior de Microfinanzas'),
(105, 'Instituto Superior de Seguridad Especializada'),
(106, 'Instituto Superior de Sistema Computarizado y Docencia'),
(46, 'Instituto Superior Especializado de Artes y Folklore'),
(53, 'Instituto Superior Flightmaxx Corporation'),
(97, 'Instituto Superior Heli Training Panamá'),
(42, 'Instituto Superior Helicópteros Personales “Flight School Division”'),
(63, 'Instituto Superior Helipan Corp.'),
(103, 'Instituto Superior IGA Panamá'),
(71, 'Instituto Superior Integral del Éxito'),
(94, 'Instituto Superior Istmeño'),
(102, 'Instituto Superior Latinoamericano de Administración y Tecnología Naval'),
(73, 'Instituto Superior Los Llanos'),
(43, 'Instituto Superior Mag Flight Training'),
(85, 'Instituto Superior Maritime Profesional of Panamá'),
(79, 'Instituto Superior Nueva Visión'),
(112, 'Instituto Superior Panamá Community College'),
(113, 'Instituto Superior Panamá Tech'),
(82, 'Instituto Superior para la Capacitación'),
(44, 'Instituto Superior Policial “Presidente Belisario Porras”'),
(33, 'Instituto Superior Politécnico de América'),
(50, 'Instituto Superior Politécnico Internacional'),
(76, 'Instituto Superior Publies Educa'),
(99, 'Instituto Superior STG Flight & Services'),
(62, 'Instituto Superior TAGUA'),
(86, 'Instituto Superior Tecnológico del Claustro Gómez'),
(41, 'Instituto Superior The Panamá Internacional Hotel School'),
(36, 'Instituto Técnico Superior Bilingüe Tecno Plus Monterrey'),
(56, 'Instituto Técnico Superior by TAC'),
(28, 'Instituto Técnico Superior de Cocina'),
(65, 'Instituto Técnico Superior de Panamá'),
(64, 'Instituto Técnico Superior Kaleo'),
(55, 'Instituto Técnico Superior Panameño'),
(54, 'Instituto Técnico Superior San Pablo Apóstol'),
(48, 'Instituto Técnico Superior SHADDAI'),
(11, 'ISAE Universidad'),
(19, 'Isthmus - Escuela de Arquitectura y Diseño'),
(1, 'Otra institución'),
(16, 'Quality Leadership University'),
(14, 'Universidad Americana'),
(4, 'Universidad Autónoma de Chiriquí'),
(7, 'Universidad Católica Santa María La Antigua'),
(2, 'Universidad de Panamá'),
(18, 'Universidad del Arte Ganexa'),
(10, 'Universidad del Istmo'),
(5, 'Universidad Especializada de las Américas'),
(17, 'Universidad Especializada del Contador Público Autorizado'),
(9, 'Universidad Interamericana de Panamá'),
(22, 'Universidad Internacional de Ciencia y Tecnología'),
(8, 'Universidad Latina de Panamá'),
(21, 'Universidad Latinoamericana de Comercio Exterior'),
(6, 'Universidad Marítima Internacional de Panamá'),
(12, 'Universidad Metropolitana de Educación, Ciencia y Tecnología'),
(3, 'Universidad Tecnológica de Panamá');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `ruta_documento`
--

CREATE TABLE `ruta_documento` (
  `idRutadoc` int(11) NOT NULL,
  `idDocumentoPostulante` int(11) NOT NULL,
  `ruta` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `documento_postulante`
--
ALTER TABLE `documento_postulante`
  ADD PRIMARY KEY (`idDocumentoPostulante`),
  ADD KEY `fk_gradoAcademico` (`idGradoEst`),
  ADD KEY `fk_institucion` (`institucion`);

--
-- Indices de la tabla `gradoacademico_documento`
--
ALTER TABLE `gradoacademico_documento`
  ADD PRIMARY KEY (`idGradoEst`),
  ADD UNIQUE KEY `nombreGradoEst` (`nombreGradoEst`);

--
-- Indices de la tabla `instituciones`
--
ALTER TABLE `instituciones`
  ADD PRIMARY KEY (`idInstitucion`),
  ADD UNIQUE KEY `nombreInstitucion` (`nombreInstitucion`);

--
-- Indices de la tabla `ruta_documento`
--
ALTER TABLE `ruta_documento`
  ADD PRIMARY KEY (`idRutadoc`),
  ADD KEY `fk_documentoPostulante` (`idDocumentoPostulante`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `documento_postulante`
--
ALTER TABLE `documento_postulante`
  MODIFY `idDocumentoPostulante` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `gradoacademico_documento`
--
ALTER TABLE `gradoacademico_documento`
  MODIFY `idGradoEst` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT de la tabla `instituciones`
--
ALTER TABLE `instituciones`
  MODIFY `idInstitucion` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=114;

--
-- AUTO_INCREMENT de la tabla `ruta_documento`
--
ALTER TABLE `ruta_documento`
  MODIFY `idRutadoc` int(11) NOT NULL AUTO_INCREMENT;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `documento_postulante`
--
ALTER TABLE `documento_postulante`
  ADD CONSTRAINT `fk_gradoAcademico` FOREIGN KEY (`idGradoEst`) REFERENCES `gradoacademico_documento` (`idGradoEst`),
  ADD CONSTRAINT `fk_institucion` FOREIGN KEY (`institucion`) REFERENCES `instituciones` (`idInstitucion`);

--
-- Filtros para la tabla `ruta_documento`
--
ALTER TABLE `ruta_documento`
  ADD CONSTRAINT `fk_documentoPostulante` FOREIGN KEY (`idDocumentoPostulante`) REFERENCES `documento_postulante` (`idDocumentoPostulante`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
