-- Costo del producto al momento de cada venta (para calcular la utilidad real). Idempotente.
-- Las ventas anteriores quedan en NULL y se valoran con el costo actual del producto.
ALTER TABLE `detalle_ventas`
  ADD COLUMN IF NOT EXISTS `costo_unit` DECIMAL(12,4) NULL COMMENT 'costo real por unidad al vender';
