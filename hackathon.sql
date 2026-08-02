-- phpMyAdmin SQL Dump
-- version 5.2.1deb3
-- https://www.phpmyadmin.net/
--
-- Servidor: localhost:3306
-- Tiempo de generación: 13-11-2025 a las 13:26:08
-- Versión del servidor: 8.0.43-0ubuntu0.24.04.2
-- Versión de PHP: 8.3.6

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `hackathon`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `configuracion_hackathon`
--

CREATE TABLE `configuracion_hackathon` (
  `id` int NOT NULL,
  `hackathon_iniciado` tinyint(1) DEFAULT '0',
  `tiempo_inicio_global` datetime DEFAULT NULL,
  `duracion_minutos` int DEFAULT '90',
  `creado_en` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf32 COLLATE=utf32_spanish_ci;

--
-- Volcado de datos para la tabla `configuracion_hackathon`
--

INSERT INTO `configuracion_hackathon` (`id`, `hackathon_iniciado`, `tiempo_inicio_global`, `duracion_minutos`, `creado_en`) VALUES
(1, 1, '2025-11-12 14:59:37', 5, '2025-10-30 13:44:43');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `desafios_completados`
--

CREATE TABLE `desafios_completados` (
  `id` int NOT NULL,
  `equipo_id` int DEFAULT NULL,
  `desafio_id` varchar(50) COLLATE utf32_spanish_ci DEFAULT NULL,
  `completado_en` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf32 COLLATE=utf32_spanish_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `equipos`
--

CREATE TABLE `equipos` (
  `id` int NOT NULL,
  `nombre_equipo` varchar(100) COLLATE utf32_spanish_ci NOT NULL,
  `codigo_equipo` varchar(10) COLLATE utf32_spanish_ci NOT NULL,
  `tiempo_inicio` datetime DEFAULT NULL,
  `puntuacion_total` int DEFAULT '0',
  `inicio_tardio` tinyint(1) DEFAULT '0',
  `creado_en` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `estado` tinyint DEFAULT '0' COMMENT '0: En espera, 1: Compitiendo',
  `actualizado_en` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `tiempo_acumulado` int DEFAULT '0',
  `tiempo_finalizacion` datetime DEFAULT NULL,
  `desafios_completados` int DEFAULT '0',
  `completado` tinyint(1) DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf32 COLLATE=utf32_spanish_ci;

--
-- Volcado de datos para la tabla `equipos`
--

INSERT INTO `equipos` (`id`, `nombre_equipo`, `codigo_equipo`, `tiempo_inicio`, `puntuacion_total`, `inicio_tardio`, `creado_en`, `estado`, `actualizado_en`, `tiempo_acumulado`, `tiempo_finalizacion`, `desafios_completados`, `completado`) VALUES
(33, 'ANGELES DE INFORMATICA', 'WY4UEQ', '2025-11-12 14:59:37', 0, 0, '2025-11-11 14:40:01', 1, '2025-11-12 14:59:37', 0, NULL, 0, 0),
(34, 'Prueba', 'BVPFGD', '2025-11-12 14:59:37', 0, 0, '2025-11-11 17:54:56', 1, '2025-11-12 14:59:37', 0, NULL, 0, 0);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `participantes`
--

CREATE TABLE `participantes` (
  `id` int NOT NULL,
  `nombre` varchar(100) COLLATE utf32_spanish_ci NOT NULL,
  `cedula` varchar(20) COLLATE utf32_spanish_ci NOT NULL,
  `equipo_id` int DEFAULT NULL,
  `creado_en` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf32 COLLATE=utf32_spanish_ci;

--
-- Volcado de datos para la tabla `participantes`
--

INSERT INTO `participantes` (`id`, `nombre`, `cedula`, `equipo_id`, `creado_en`) VALUES
(64, 'jose', '10101010', 33, '2025-11-11 14:40:01'),
(65, 'angel', '20202020', 33, '2025-11-11 14:40:01'),
(66, 'OTRO', '303030', 33, '2025-11-11 14:40:01'),
(67, 'OTROS', '505050', 33, '2025-11-11 14:40:01'),
(68, 'Jsudbsj', '30692052', 34, '2025-11-11 17:54:56'),
(69, 'Bcksbxj', '98765555', 34, '2025-11-11 17:54:56'),
(70, 'Kslakdosj', '1738172', 34, '2025-11-11 17:54:56'),
(71, 'Oaldmaal', '99988877', 34, '2025-11-11 17:54:56');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `configuracion_hackathon`
--
ALTER TABLE `configuracion_hackathon`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `desafios_completados`
--
ALTER TABLE `desafios_completados`
  ADD PRIMARY KEY (`id`),
  ADD KEY `equipo_id` (`equipo_id`);

--
-- Indices de la tabla `equipos`
--
ALTER TABLE `equipos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nombre_equipo` (`nombre_equipo`),
  ADD UNIQUE KEY `codigo_equipo` (`codigo_equipo`);

--
-- Indices de la tabla `participantes`
--
ALTER TABLE `participantes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `cedula` (`cedula`),
  ADD KEY `equipo_id` (`equipo_id`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `configuracion_hackathon`
--
ALTER TABLE `configuracion_hackathon`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `desafios_completados`
--
ALTER TABLE `desafios_completados`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=195;

--
-- AUTO_INCREMENT de la tabla `equipos`
--
ALTER TABLE `equipos`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;

--
-- AUTO_INCREMENT de la tabla `participantes`
--
ALTER TABLE `participantes`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=72;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `desafios_completados`
--
ALTER TABLE `desafios_completados`
  ADD CONSTRAINT `desafios_completados_ibfk_1` FOREIGN KEY (`equipo_id`) REFERENCES `equipos` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `participantes`
--
ALTER TABLE `participantes`
  ADD CONSTRAINT `participantes_ibfk_1` FOREIGN KEY (`equipo_id`) REFERENCES `equipos` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
