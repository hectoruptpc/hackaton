-- phpMyAdmin SQL Dump
-- version 5.2.1deb3
-- https://www.phpmyadmin.net/
--
-- Servidor: localhost:3306
-- Tiempo de generación: 31-10-2025 a las 17:24:29
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
(1, 0, NULL, 2, '2025-10-30 13:44:43');

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
  `actualizado_en` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf32 COLLATE=utf32_spanish_ci;

--
-- Volcado de datos para la tabla `equipos`
--

INSERT INTO `equipos` (`id`, `nombre_equipo`, `codigo_equipo`, `tiempo_inicio`, `puntuacion_total`, `inicio_tardio`, `creado_en`, `estado`, `actualizado_en`) VALUES
(28, 'El fantasma de la puerta', '68EZO9', NULL, 0, 0, '2025-10-31 16:32:08', 0, '2025-10-31 17:24:15'),
(29, 'Los cara de papa', 'E02N2M', NULL, 0, 0, '2025-10-31 16:33:18', 0, '2025-10-31 17:24:15');

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
(48, 'Hector', '30692052', 28, '2025-10-31 16:32:09'),
(49, 'Juan', '98765555', 28, '2025-10-31 16:32:09'),
(50, 'Luis', '1738172', 28, '2025-10-31 16:32:09'),
(51, 'Carlos', '99988877', 28, '2025-10-31 16:32:09'),
(52, 'Pepe', '12345555', 29, '2025-10-31 16:33:19'),
(53, 'Susi', '2737283', 29, '2025-10-31 16:33:19'),
(54, 'Lola', '56789111', 29, '2025-10-31 16:33:19'),
(55, 'Petra', '12345678', 29, '2025-10-31 16:33:19');

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
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de la tabla `equipos`
--
ALTER TABLE `equipos`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT de la tabla `participantes`
--
ALTER TABLE `participantes`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=56;

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
