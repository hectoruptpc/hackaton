-- Script de inicialización para la base de datos hackathon
-- Diseñado para ejecutarse varias veces sin romperse.

SET NAMES utf8mb4;
SET SQL_MODE = 'NO_AUTO_VALUE_ON_ZERO';
START TRANSACTION;

CREATE DATABASE IF NOT EXISTS `hackathon`
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE `hackathon`;

CREATE TABLE IF NOT EXISTS `configuracion_hackathon` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `hackathon_iniciado` TINYINT(1) DEFAULT 0,
  `tiempo_inicio_global` DATETIME DEFAULT NULL,
  `duracion_minutos` INT DEFAULT 90,
  `creado_en` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `desafios_completados` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `equipo_id` INT DEFAULT NULL,
  `desafio_id` VARCHAR(50) DEFAULT NULL,
  `completado_en` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `equipo_id` (`equipo_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `equipos` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `nombre_equipo` VARCHAR(100) NOT NULL,
  `codigo_equipo` VARCHAR(10) NOT NULL,
  `tiempo_inicio` DATETIME DEFAULT NULL,
  `puntuacion_total` INT DEFAULT 0,
  `inicio_tardio` TINYINT(1) DEFAULT 0,
  `creado_en` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `estado` TINYINT DEFAULT 0 COMMENT '0: En espera, 1: Compitiendo',
  `actualizado_en` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `tiempo_acumulado` INT DEFAULT 0,
  `tiempo_finalizacion` DATETIME DEFAULT NULL,
  `desafios_completados` INT DEFAULT 0,
  `completado` TINYINT(1) DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `nombre_equipo` (`nombre_equipo`),
  UNIQUE KEY `codigo_equipo` (`codigo_equipo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `participantes` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `nombre` VARCHAR(100) NOT NULL,
  `cedula` VARCHAR(20) NOT NULL,
  `equipo_id` INT DEFAULT NULL,
  `creado_en` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `cedula` (`cedula`),
  KEY `equipo_id` (`equipo_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE `configuracion_hackathon`
  ADD COLUMN IF NOT EXISTS `hackathon_iniciado` TINYINT(1) DEFAULT 0 AFTER `id`,
  ADD COLUMN IF NOT EXISTS `tiempo_inicio_global` DATETIME DEFAULT NULL AFTER `hackathon_iniciado`,
  ADD COLUMN IF NOT EXISTS `duracion_minutos` INT DEFAULT 90 AFTER `tiempo_inicio_global`,
  ADD COLUMN IF NOT EXISTS `creado_en` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP AFTER `duracion_minutos`;

ALTER TABLE `configuracion_hackathon`
  MODIFY COLUMN `id` INT NOT NULL AUTO_INCREMENT,
  MODIFY COLUMN `hackathon_iniciado` TINYINT(1) DEFAULT 0,
  MODIFY COLUMN `tiempo_inicio_global` DATETIME DEFAULT NULL,
  MODIFY COLUMN `duracion_minutos` INT DEFAULT 90,
  MODIFY COLUMN `creado_en` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP;

ALTER TABLE `desafios_completados`
  ADD COLUMN IF NOT EXISTS `equipo_id` INT DEFAULT NULL AFTER `id`,
  ADD COLUMN IF NOT EXISTS `desafio_id` VARCHAR(50) DEFAULT NULL AFTER `equipo_id`,
  ADD COLUMN IF NOT EXISTS `completado_en` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP AFTER `desafio_id`;

ALTER TABLE `desafios_completados`
  MODIFY COLUMN `id` INT NOT NULL AUTO_INCREMENT,
  MODIFY COLUMN `equipo_id` INT DEFAULT NULL,
  MODIFY COLUMN `desafio_id` VARCHAR(50) DEFAULT NULL,
  MODIFY COLUMN `completado_en` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP;

ALTER TABLE `equipos`
  ADD COLUMN IF NOT EXISTS `nombre_equipo` VARCHAR(100) NOT NULL AFTER `id`,
  ADD COLUMN IF NOT EXISTS `codigo_equipo` VARCHAR(10) NOT NULL AFTER `nombre_equipo`,
  ADD COLUMN IF NOT EXISTS `tiempo_inicio` DATETIME DEFAULT NULL AFTER `codigo_equipo`,
  ADD COLUMN IF NOT EXISTS `puntuacion_total` INT DEFAULT 0 AFTER `tiempo_inicio`,
  ADD COLUMN IF NOT EXISTS `inicio_tardio` TINYINT(1) DEFAULT 0 AFTER `puntuacion_total`,
  ADD COLUMN IF NOT EXISTS `creado_en` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP AFTER `inicio_tardio`,
  ADD COLUMN IF NOT EXISTS `estado` TINYINT DEFAULT 0 COMMENT '0: En espera, 1: Compitiendo' AFTER `creado_en`,
  ADD COLUMN IF NOT EXISTS `actualizado_en` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER `estado`,
  ADD COLUMN IF NOT EXISTS `tiempo_acumulado` INT DEFAULT 0 AFTER `actualizado_en`,
  ADD COLUMN IF NOT EXISTS `tiempo_finalizacion` DATETIME DEFAULT NULL AFTER `tiempo_acumulado`,
  ADD COLUMN IF NOT EXISTS `desafios_completados` INT DEFAULT 0 AFTER `tiempo_finalizacion`,
  ADD COLUMN IF NOT EXISTS `completado` TINYINT(1) DEFAULT 0 AFTER `desafios_completados`;

ALTER TABLE `equipos`
  MODIFY COLUMN `id` INT NOT NULL AUTO_INCREMENT,
  MODIFY COLUMN `nombre_equipo` VARCHAR(100) NOT NULL,
  MODIFY COLUMN `codigo_equipo` VARCHAR(10) NOT NULL,
  MODIFY COLUMN `tiempo_inicio` DATETIME DEFAULT NULL,
  MODIFY COLUMN `puntuacion_total` INT DEFAULT 0,
  MODIFY COLUMN `inicio_tardio` TINYINT(1) DEFAULT 0,
  MODIFY COLUMN `creado_en` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  MODIFY COLUMN `estado` TINYINT DEFAULT 0 COMMENT '0: En espera, 1: Compitiendo',
  MODIFY COLUMN `actualizado_en` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  MODIFY COLUMN `tiempo_acumulado` INT DEFAULT 0,
  MODIFY COLUMN `tiempo_finalizacion` DATETIME DEFAULT NULL,
  MODIFY COLUMN `desafios_completados` INT DEFAULT 0,
  MODIFY COLUMN `completado` TINYINT(1) DEFAULT 0;

ALTER TABLE `participantes`
  ADD COLUMN IF NOT EXISTS `nombre` VARCHAR(100) NOT NULL AFTER `id`,
  ADD COLUMN IF NOT EXISTS `cedula` VARCHAR(20) NOT NULL AFTER `nombre`,
  ADD COLUMN IF NOT EXISTS `equipo_id` INT DEFAULT NULL AFTER `cedula`,
  ADD COLUMN IF NOT EXISTS `creado_en` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP AFTER `equipo_id`;

ALTER TABLE `participantes`
  MODIFY COLUMN `id` INT NOT NULL AUTO_INCREMENT,
  MODIFY COLUMN `nombre` VARCHAR(100) NOT NULL,
  MODIFY COLUMN `cedula` VARCHAR(20) NOT NULL,
  MODIFY COLUMN `equipo_id` INT DEFAULT NULL,
  MODIFY COLUMN `creado_en` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP;

SET @fk_exists := (
  SELECT COUNT(*)
  FROM information_schema.KEY_COLUMN_USAGE
  WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'desafios_completados'
    AND CONSTRAINT_NAME = 'desafios_completados_ibfk_1'
);
SET @fk_sql := IF(@fk_exists = 0,
  'ALTER TABLE `desafios_completados` ADD CONSTRAINT `desafios_completados_ibfk_1` FOREIGN KEY (`equipo_id`) REFERENCES `equipos` (`id`) ON DELETE CASCADE',
  'SELECT 1'
);
PREPARE stmt FROM @fk_sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @fk_exists := (
  SELECT COUNT(*)
  FROM information_schema.KEY_COLUMN_USAGE
  WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'participantes'
    AND CONSTRAINT_NAME = 'participantes_ibfk_1'
);
SET @fk_sql := IF(@fk_exists = 0,
  'ALTER TABLE `participantes` ADD CONSTRAINT `participantes_ibfk_1` FOREIGN KEY (`equipo_id`) REFERENCES `equipos` (`id`) ON DELETE CASCADE',
  'SELECT 1'
);
PREPARE stmt FROM @fk_sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

INSERT INTO `configuracion_hackathon` (`id`, `hackathon_iniciado`, `tiempo_inicio_global`, `duracion_minutos`, `creado_en`)
VALUES (1, 1, '2025-11-12 14:59:37', 5, '2025-10-30 13:44:43')
ON DUPLICATE KEY UPDATE
  `hackathon_iniciado` = VALUES(`hackathon_iniciado`),
  `tiempo_inicio_global` = VALUES(`tiempo_inicio_global`),
  `duracion_minutos` = VALUES(`duracion_minutos`),
  `creado_en` = VALUES(`creado_en`);

INSERT INTO `equipos` (`id`, `nombre_equipo`, `codigo_equipo`, `tiempo_inicio`, `puntuacion_total`, `inicio_tardio`, `creado_en`, `estado`, `actualizado_en`, `tiempo_acumulado`, `tiempo_finalizacion`, `desafios_completados`, `completado`)
VALUES
  (33, 'ANGELES DE INFORMATICA', 'WY4UEQ', '2025-11-12 14:59:37', 0, 0, '2025-11-11 14:40:01', 1, '2025-11-12 14:59:37', 0, NULL, 0, 0),
  (34, 'Prueba', 'BVPFGD', '2025-11-12 14:59:37', 0, 0, '2025-11-11 17:54:56', 1, '2025-11-12 14:59:37', 0, NULL, 0, 0)
ON DUPLICATE KEY UPDATE
  `nombre_equipo` = VALUES(`nombre_equipo`),
  `codigo_equipo` = VALUES(`codigo_equipo`),
  `tiempo_inicio` = VALUES(`tiempo_inicio`),
  `puntuacion_total` = VALUES(`puntuacion_total`),
  `inicio_tardio` = VALUES(`inicio_tardio`),
  `creado_en` = VALUES(`creado_en`),
  `estado` = VALUES(`estado`),
  `actualizado_en` = VALUES(`actualizado_en`),
  `tiempo_acumulado` = VALUES(`tiempo_acumulado`),
  `tiempo_finalizacion` = VALUES(`tiempo_finalizacion`),
  `desafios_completados` = VALUES(`desafios_completados`),
  `completado` = VALUES(`completado`);

INSERT INTO `participantes` (`id`, `nombre`, `cedula`, `equipo_id`, `creado_en`)
VALUES
  (64, 'jose', '10101010', 33, '2025-11-11 14:40:01'),
  (65, 'angel', '20202020', 33, '2025-11-11 14:40:01'),
  (66, 'OTRO', '303030', 33, '2025-11-11 14:40:01'),
  (67, 'OTROS', '505050', 33, '2025-11-11 14:40:01'),
  (68, 'Jsudbsj', '30692052', 34, '2025-11-11 17:54:56'),
  (69, 'Bcksbxj', '98765555', 34, '2025-11-11 17:54:56'),
  (70, 'Kslakdosj', '1738172', 34, '2025-11-11 17:54:56'),
  (71, 'Oaldmaal', '99988877', 34, '2025-11-11 17:54:56')
ON DUPLICATE KEY UPDATE
  `nombre` = VALUES(`nombre`),
  `cedula` = VALUES(`cedula`),
  `equipo_id` = VALUES(`equipo_id`),
  `creado_en` = VALUES(`creado_en`);

ALTER TABLE `configuracion_hackathon` AUTO_INCREMENT = 2;
ALTER TABLE `desafios_completados` AUTO_INCREMENT = 195;
ALTER TABLE `equipos` AUTO_INCREMENT = 35;
ALTER TABLE `participantes` AUTO_INCREMENT = 72;

COMMIT;
