-- ============================================================
-- Módulos y permisos por rol.
-- Idempotente: se puede ejecutar sobre una BD nueva (tras 00_schema_insumos.sql)
-- o sobre una BD existente sin perder datos.
--   modulos       catálogo de módulos; `activo` = interruptor global.
--   roles         tipos de usuario (es_admin = acceso total a módulos activos).
--   rol_permisos  matriz rol x módulo: ver / crear / editar / anular.
--   usuarios.rol_id  rol asignado (usuarios.rol se mantiene por compatibilidad).
-- ============================================================

CREATE TABLE IF NOT EXISTS `modulos` (
  `id_modulo`   INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `clave`       VARCHAR(30)  NOT NULL,
  `nombre`      VARCHAR(60)  NOT NULL,
  `descripcion` VARCHAR(200) NULL,
  `icono`       VARCHAR(40)  NOT NULL DEFAULT 'ph-squares-four' COMMENT 'Phosphor Icons',
  `tono`        SMALLINT     NOT NULL DEFAULT 37 COMMENT 'hue oklch del ícono',
  `url`         VARCHAR(80)  NOT NULL COMMENT 'página de entrada',
  `menu`        TINYINT(1)   NOT NULL DEFAULT 1 COMMENT '1 = aparece en la barra superior',
  `orden`       SMALLINT     NOT NULL DEFAULT 0,
  `depende`     VARCHAR(120) NULL COMMENT 'claves separadas por coma',
  `nucleo`      TINYINT(1)   NOT NULL DEFAULT 0 COMMENT '1 = no se puede apagar',
  `activo`      TINYINT(1)   NOT NULL DEFAULT 1,
  PRIMARY KEY (`id_modulo`),
  UNIQUE KEY `uq_clave` (`clave`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

CREATE TABLE IF NOT EXISTS `roles` (
  `id_rol`      INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `nombre`      VARCHAR(40)  NOT NULL,
  `descripcion` VARCHAR(160) NULL,
  `es_admin`    TINYINT(1)   NOT NULL DEFAULT 0,
  `estado`      TINYINT(1)   NOT NULL DEFAULT 1,
  PRIMARY KEY (`id_rol`),
  UNIQUE KEY `uq_nombre` (`nombre`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

CREATE TABLE IF NOT EXISTS `rol_permisos` (
  `id_rol`    INT UNSIGNED NOT NULL,
  `id_modulo` INT UNSIGNED NOT NULL,
  `ver`       TINYINT(1) NOT NULL DEFAULT 0,
  `crear`     TINYINT(1) NOT NULL DEFAULT 0,
  `editar`    TINYINT(1) NOT NULL DEFAULT 0,
  `anular`    TINYINT(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id_rol`,`id_modulo`),
  KEY `idx_modulo` (`id_modulo`),
  CONSTRAINT `fk_rp_rol`    FOREIGN KEY (`id_rol`)    REFERENCES `roles`   (`id_rol`)    ON DELETE CASCADE,
  CONSTRAINT `fk_rp_modulo` FOREIGN KEY (`id_modulo`) REFERENCES `modulos` (`id_modulo`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

ALTER TABLE `usuarios` ADD COLUMN IF NOT EXISTS `rol_id` INT UNSIGNED NULL AFTER `rol`;

-- ---------- Catálogo de módulos ----------
-- Activos por defecto: lo necesario para vender insumos. Apagados: referencia lista para activar.
INSERT IGNORE INTO `modulos`
  (`clave`,`nombre`,`descripcion`,`icono`,`tono`,`url`,`menu`,`orden`,`depende`,`nucleo`,`activo`) VALUES
  ('dashboard',    'Escritorio',    'Resumen de ventas, caja y stock',           'ph-house',          37,  'dashboard.php',        1, 10,  NULL,        1, 1),
  ('usuarios',     'Usuarios',      'Altas y edición de usuarios',               'ph-user-plus',      145, 'ver_usuarios.php',     1, 20,  NULL,        0, 1),
  ('clientes',     'Clientes',      'Cartera de clientes',                       'ph-users-three',    60,  'ver_clientes.php',     1, 30,  NULL,        0, 1),
  ('productos',    'Productos',     'Insumos, categorías, marcas e inventario',  'ph-package',        75,  'ver_productos.php',    1, 40,  NULL,        0, 1),
  ('ventas',       'Ventas',        'Registro y listado de ventas',              'ph-receipt',        230, 'venta.php',            1, 50,  'productos,clientes', 0, 1),
  ('caja',         'Caja',          'Cobros, pagos y cuentas por cobrar',        'ph-cash-register',  150, 'caja_pagos.php',       1, 60,  'ventas',    0, 1),
  ('compras',      'Compras',       'Compras a proveedores y cuentas por pagar', 'ph-shopping-cart',  170, 'compra.php',           1, 70,  'productos,proveedores', 0, 1),
  ('proveedores',  'Proveedores',   'Directorio de proveedores',                 'ph-truck',          200, 'proveedores.php',      0, 75,  NULL,        0, 1),
  ('gastos',       'Gastos',        'Gastos y balance',                          'ph-wallet',         120, 'gastos.php',           1, 80,  NULL,        0, 1),
  ('reportes',     'Reportes',      'Reportes de ventas, compras e inventario',  'ph-chart-line-up',  250, 'reportes.php',         1, 90,  NULL,        0, 1),
  ('facturacion',  'Facturación',   'Boletas, facturas y notas (Nubefact)',      'ph-file-text',      210, 'comprobantes.php',     1, 100, 'ventas',    0, 1),
  ('preventas',    'Preventas',     'Pedidos previos y pedidos por IA',          'ph-clipboard-text', 30,  'preventas.php',        1, 110, 'ventas,clientes', 0, 0),
  ('empleados',    'Empleados',     'Empleados, asistencia y descansos',         'ph-identification-badge', 320, 'ver_empleados.php', 1, 120, NULL,   0, 0),
  ('planillas',    'Planillas',     'Planillas, adelantos y boletas de pago',    'ph-money',          100, 'planillas.php',        1, 130, 'empleados', 0, 0),
  ('tablet',       'Tablet POS',    'Punto de venta para tablet',                'ph-device-tablet',  190, 'tablet_config_admin.php', 0, 140, 'ventas', 0, 0),
  ('auditoria',    'Auditoría',     'Registro de cambios en ventas',             'ph-clock-counter-clockwise', 280, 'logs_auditoria.php', 0, 150, NULL, 0, 1),
  ('configuracion','Configuración', 'Módulos, roles y permisos',                 'ph-gear',           210, 'modulos_config.php',   0, 900, NULL,        1, 1);

-- ---------- Roles iniciales ----------
INSERT IGNORE INTO `roles` (`nombre`,`descripcion`,`es_admin`) VALUES
  ('Administrador','Acceso total a los módulos activos',1),
  ('Operador','Ventas y caja',0);

-- Operador: lo mismo que el menú reducido original (Escritorio, Ventas, Caja).
INSERT IGNORE INTO `rol_permisos` (`id_rol`,`id_modulo`,`ver`,`crear`,`editar`,`anular`)
SELECT r.id_rol, m.id_modulo, 1, (m.clave <> 'dashboard'), 0, 0
FROM `roles` r JOIN `modulos` m ON m.clave IN ('dashboard','ventas','caja')
WHERE r.nombre = 'Operador';

-- ---------- Migrar usuarios existentes (rol texto -> rol_id) ----------
UPDATE `usuarios` u JOIN `roles` r ON r.nombre = 'Administrador'
   SET u.rol_id = r.id_rol WHERE u.rol_id IS NULL AND u.rol = 'admin';
UPDATE `usuarios` u JOIN `roles` r ON r.nombre = 'Operador'
   SET u.rol_id = r.id_rol WHERE u.rol_id IS NULL AND u.rol <> 'admin';
