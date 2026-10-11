-- Medios de pago configurables (antes fijos en el código y en un ENUM) y carpeta de datos de empresa.
-- Idempotente.

-- El ENUM impedía agregar medios nuevos: pasa a texto.
ALTER TABLE `venta_pagos` MODIFY `metodo` VARCHAR(30) NOT NULL;

CREATE TABLE IF NOT EXISTS `medios_pago` (
  `clave`    VARCHAR(30) NOT NULL,
  `nombre`   VARCHAR(40) NOT NULL,
  `icono`    VARCHAR(40) NOT NULL DEFAULT 'fa-money-bill-wave' COMMENT 'Font Awesome 5',
  `digital`  TINYINT(1)  NOT NULL DEFAULT 0 COMMENT '1 = pago electrónico (Yape, transferencia…)',
  `selector` TINYINT(1)  NOT NULL DEFAULT 1 COMMENT '1 = se ofrece al cobrar; 0 = lo genera el sistema',
  `sistema`  TINYINT(1)  NOT NULL DEFAULT 0 COMMENT '1 = no se puede borrar',
  `orden`    SMALLINT    NOT NULL DEFAULT 0,
  `activo`   TINYINT(1)  NOT NULL DEFAULT 1,
  PRIMARY KEY (`clave`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

INSERT IGNORE INTO `medios_pago` (`clave`,`nombre`,`icono`,`digital`,`selector`,`sistema`,`orden`,`activo`) VALUES
  ('efectivo',      'Efectivo',      'fa-money-bill-wave', 0, 1, 1, 10, 1),
  ('yape',          'Yape',          'fa-mobile-alt',      1, 1, 0, 20, 1),
  ('plin',          'Plin',          'fa-mobile-alt',      1, 1, 0, 30, 1),
  ('transferencia', 'Transferencia', 'fa-university',      1, 1, 0, 40, 1),
  ('tarjeta',       'Tarjeta',       'fa-credit-card',     0, 1, 0, 50, 1),
  ('planilla',      'Descuento planilla', 'fa-file-invoice', 0, 0, 1, 900, 1);

-- Los datos de la empresa (marca, razón social, RUC, dirección…) se guardan en `configuracion`
-- con los parámetros empresa_* y marca_*; se editan en Configuración → Datos de la empresa.
