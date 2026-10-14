-- Costo real de los productos (compra + flete) y gastos ligados a compras y ventas. Idempotente.

-- Contenido de un bulto (p. ej. 25 kg por bolsa) para calcular los bultos de una compra.
-- costo_real: costo promedio puesto en almacén (precio de compra + parte del flete).
ALTER TABLE `productos`
  ADD COLUMN IF NOT EXISTS `contenido_bulto` DECIMAL(10,3) NULL COMMENT 'kg, lt o und que trae un bulto',
  ADD COLUMN IF NOT EXISTS `costo_real` DECIMAL(12,4) NULL COMMENT 'costo promedio puesto en almacén';

ALTER TABLE `detalle_compras`
  ADD COLUMN IF NOT EXISTS `costo_adicional` DECIMAL(12,2) NOT NULL DEFAULT 0 COMMENT 'parte de flete y otros costos de esta línea',
  ADD COLUMN IF NOT EXISTS `costo_unit_real` DECIMAL(12,4) NULL COMMENT 'costo por unidad base con flete incluido';

-- Costos adicionales de una compra (flete entre ciudades, descarga…).
CREATE TABLE IF NOT EXISTS `compra_costos` (
  `id_costo`  INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `compra`    INT NOT NULL,
  `concepto`  VARCHAR(60) NOT NULL,
  `modo`      ENUM('bulto','fijo_cantidad','fijo_valor','unidad') NOT NULL DEFAULT 'bulto',
  `tarifa`    DECIMAL(12,2) NOT NULL DEFAULT 0 COMMENT 'tarifa por bulto/unidad o monto fijo',
  `bultos`    DECIMAL(10,2) NULL,
  `monto`     DECIMAL(12,2) NOT NULL,
  `id_gasto`  INT UNSIGNED NULL COMMENT 'gasto que registra el pago al transportista',
  PRIMARY KEY (`id_costo`),
  KEY `idx_compra` (`compra`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- Un gasto puede pertenecer a una compra (flete) o a una venta (entrega local).
ALTER TABLE `gastos`
  ADD COLUMN IF NOT EXISTS `id_compra` INT NULL,
  ADD COLUMN IF NOT EXISTS `id_venta`  INT NULL;
