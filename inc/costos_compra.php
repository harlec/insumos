<?php
// Costos adicionales de una compra (flete entre ciudades, descarga…) y costo real por producto.
//  - Cada costo se reparte entre las líneas de la compra y se suma al costo de cada producto.
//  - Se paga al contado: queda registrado como gasto (sale de caja por el medio elegido).
//  - El costo real del producto es el promedio ponderado entre el stock que había y lo que entra.
require_once __DIR__ . '/config.php';

const COSTO_MODOS = ['bulto', 'fijo_cantidad', 'fijo_valor', 'unidad'];

// Bultos de una línea: cantidad / contenido por bulto (redondeado hacia arriba); sin contenido, 1 bulto por unidad.
function costo_bultos_linea($cantidad, $contenido) {
	$contenido = (float)$contenido;
	return $contenido > 0 ? ceil(((float)$cantidad / $contenido) - 1e-9) : (float)$cantidad;
}

// $lineas: [['pid','cant','valor','stock_antes','costo_antes','contenido','id_detalle'], ...]
function compra_aplicar_costos($db, $id_compra, array $lineas, array $post, $usuario, $fecha, $metodos_validos) {
	$n = count($lineas);
	if ($n === 0) return;
	$extra = array_fill(0, $n, 0.0);

	$conceptos = $post['costo_concepto'] ?? [];
	$modos     = $post['costo_modo'] ?? [];
	$valores   = $post['costo_valor'] ?? [];
	$bultos_in = $post['costo_bultos'] ?? [];
	$metodo    = in_array($post['costo_metodo'] ?? '', $metodos_validos, true) ? $post['costo_metodo'] : 'efectivo';

	$cant = []; $val = []; $bul = [];
	foreach ($lineas as $k => $l) {
		$cant[$k] = (float)$l['cant']; $val[$k] = (float)$l['valor'];
		$bul[$k]  = costo_bultos_linea($l['cant'], $l['contenido']);
	}
	$total_cant = array_sum($cant);

	foreach ((array)$valores as $i => $v) {
		$tarifa = round(max(0, (float)$v), 2);
		$modo = in_array($modos[$i] ?? '', COSTO_MODOS, true) ? $modos[$i] : 'bulto';
		if ($tarifa <= 0) continue;
		$concepto = trim((string)($conceptos[$i] ?? '')); if ($concepto === '') $concepto = 'Flete';
		$concepto = mb_substr($concepto, 0, 60);

		$bultos_total = null;
		switch ($modo) {
			case 'bulto':
				$bultos_total = (isset($bultos_in[$i]) && $bultos_in[$i] !== '') ? max(0, (float)$bultos_in[$i]) : array_sum($bul);
				$monto = round($tarifa * $bultos_total, 2); $pesos = $bul; break;
			case 'fijo_valor':
				$monto = $tarifa; $pesos = $val; break;
			case 'unidad':
				$monto = round($tarifa * $total_cant, 2); $pesos = $cant; break;
			default: // fijo_cantidad
				$monto = $tarifa; $pesos = $cant;
		}
		if ($monto <= 0) continue;
		$suma = array_sum($pesos);
		foreach ($lineas as $k => $_) {
			$extra[$k] += $suma > 0 ? $monto * $pesos[$k] / $suma : $monto / $n;
		}

		// Pago al contado al transportista: gasto ligado a la compra (para la caja y el balance).
		$cat = mb_substr('Compra: ' . $concepto, 0, 60);
		$desc = 'Compra #' . $id_compra . ' - ' . $concepto;
		$st = $db->prepare("INSERT INTO gastos (fecha, tipo, categoria, descripcion, monto, metodo, usuario, estado, fecha_registro, id_compra) VALUES (?, 'puntual', ?, ?, ?, ?, ?, '1', NOW(), ?)");
		$st->bind_param('sssdsii', $fecha, $cat, $desc, $monto, $metodo, $usuario, $id_compra);
		$st->execute();
		$id_gasto = (int)$db->insert_id;

		$st = $db->prepare("INSERT INTO compra_costos (compra, concepto, modo, tarifa, bultos, monto, id_gasto) VALUES (?, ?, ?, ?, ?, ?, ?)");
		$st->bind_param('issdddi', $id_compra, $concepto, $modo, $tarifa, $bultos_total, $monto, $id_gasto);
		$st->execute();
	}

	// Costo real por línea y promedio ponderado del producto.
	foreach ($lineas as $k => $l) {
		if ($cant[$k] <= 0) continue;
		$extra_k = round($extra[$k], 2);
		$unit = round(($val[$k] + $extra_k) / $cant[$k], 4);
		$st = $db->prepare("UPDATE detalle_compras SET costo_adicional = ?, costo_unit_real = ? WHERE id_de_compra = ?");
		$idd = (int)$l['id_detalle'];
		$st->bind_param('ddi', $extra_k, $unit, $idd);
		$st->execute();

		$stock_antes = max(0.0, (float)$l['stock_antes']);
		$antes = $l['costo_antes'] !== null ? (float)$l['costo_antes'] : null;
		$nuevo = ($antes !== null && $stock_antes > 0)
			? round(($stock_antes * $antes + $cant[$k] * $unit) / ($stock_antes + $cant[$k]), 4)
			: $unit;
		$st = $db->prepare("UPDATE productos SET costo_real = ? WHERE id_producto = ?");
		$pid = (int)$l['pid'];
		$st->bind_param('di', $nuevo, $pid);
		$st->execute();
	}
}
