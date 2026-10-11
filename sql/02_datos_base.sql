-- Datos mínimos para operar desde cero (idempotente).
-- El alta de productos asigna el proveedor 1 por defecto: debe existir (llave foránea).
INSERT IGNORE INTO `proveedores` (`id_proveedor`,`proveedor`,`doc_identidad`,`direccion`,`telefono`,`email`,`estado`)
VALUES (1,'SIN PROVEEDOR','','','','','1');

INSERT IGNORE INTO `categorias` (`id_categoria`,`nom_cat`,`estado`) VALUES (1,'GENERAL','1');
