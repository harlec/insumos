<?php
// Los insumos se venden en una sola unidad (und, kg o lt): sin variantes.
// El resto del sistema (ventas, compras, tablet) trabaja con variante_p; por eso cada producto
// mantiene UNA fila interna con cantidad 1, cuyos precios siguen a los del producto.
require_once __DIR__ . '/config.php';

function variante_unica_sincronizar($producto, $precio_venta, $precio_compra) {
	$db = db_connect();
	if ($db->connect_errno) return false;
	$db->set_charset('utf8');
	$producto = (int)$producto;
	$pv = (float)$precio_venta;
	$pc = (float)$precio_compra;

	// Variante por defecto (se crea una sola vez para todo el sistema).
	$r = $db->query("SELECT id_variante FROM variantes WHERE variante = 'UNIDAD' ORDER BY id_variante LIMIT 1");
	$f = $r ? $r->fetch_row() : null;
	if ($f) { $vid = (int)$f[0]; }
	else {
		$db->query("INSERT INTO variantes (variante, cantidad, estado) VALUES ('UNIDAD', '1', '1')");
		$vid = (int)$db->insert_id;
	}

	$st = $db->prepare("SELECT id_vp FROM variante_p WHERE producto_vp = ? AND variante_vp = ? ORDER BY id_vp LIMIT 1");
	$st->bind_param('ii', $producto, $vid); $st->execute();
	$row = $st->get_result()->fetch_row();
	if ($row) {
		$idvp = (int)$row[0];
		$st = $db->prepare("UPDATE variante_p SET cantidad_vp = '1', precio_vp = ?, precioc_vp = ?, state_vp = '1' WHERE id_vp = ?");
		$st->bind_param('ddi', $pv, $pc, $idvp);
	} else {
		$st = $db->prepare("INSERT INTO variante_p (producto_vp, variante_vp, cantidad_vp, precio_vp, precioc_vp, state_vp) VALUES (?, ?, '1', ?, ?, '1')");
		$st->bind_param('iidd', $producto, $vid, $pv, $pc);
	}
	$ok = $st->execute();
	$db->close();
	return $ok;
}
