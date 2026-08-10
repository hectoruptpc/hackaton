-- ============================================================
-- SQL de la Tabla Discreta sys_cfg (Protección de Integridad UPTPC)
-- ============================================================

CREATE TABLE IF NOT EXISTS `sys_cfg` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `k` VARCHAR(100) NOT NULL UNIQUE,
  `l` INT NOT NULL,
  `s` INT NOT NULL,
  `h` VARCHAR(64) NOT NULL,
  `t` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

TRUNCATE TABLE `sys_cfg`;

INSERT INTO `sys_cfg` (`k`, `l`, `s`, `h`) VALUES
('conf/header.php', 112, 6426, 'cb30b1cb7bac552e3603f6785b8ff3713f497245ee4f82403849baf920bcb28d'),
('conf/footer.php', 95, 5909, '6a35568c7344b99bfd14d46728edce360641e9d54ea7eeb41dc45e4a38feaec4'),
('index.php', 1482, 69641, '34efa135ff437765c3f96448f687c536e7266315b8e0bb3ef5bd86256a38c29d'),
('equipos.php', 2447, 106745, '5b09716096c2b739a4d76c2356e8c6352adafd8e157cb2884ef6fc420b8f0524'),
('robo_banco.php', 879, 48755, 'c1415daa70a610c51846cff3b6dddf1e2e12f6f968af5867e8e6af505904cb77'),
('conf/functions.php', 1221, 39750, '4aebf254c658e2a2c831a4f79a492e9997788e0b9cb7a3938fe748f52ef6d250');
