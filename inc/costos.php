<?php
// Costo de ventas y utilidad. Requiere sql/05 y sql/06.
require_once __DIR__ . '/config.php';

// Guarda en cada línea de la venta el costo real del producto en ese momento.
function ventas_fijar_costos($conn, $venta_id) {
	if ($conn === null) $conn = db_connect();
	$venta_id = (int)$venta_id;
	@$conn->query("UPDATE detalle_ventas dv JOIN productos p ON p.id_producto = dv.producto
	               SET dv.costo_unit = COALESCE(NULLIF(p.costo_real, 0), NULLIF(p.precio_compra + 0, 0))
	               WHERE dv.venta = $venta_id AND dv.costo_unit IS NULL");
}

// Utilidad devengada del periodo (fechas 'Y-m-d'):
//   ventas − costo de lo vendido − gastos. Los fletes de compra no cuentan como gasto porque ya están
//   dentro del costo del producto; la entrega local (taxi) sí es gasto de venta.
function utilidad_periodo($conn, $ini, $fin) {
	$ini = $conn->real_escape_string($ini); $fin = $conn->real_escape_string($fin);
	$costo_expr = "COALESCE(dv.costo_unit, NULLIF(p.costo_real, 0), NULLIF(p.precio_compra + 0, 0))";
	$out = ['ventas' => 0.0, 'costo' => 0.0, 'bruta' => 0.0, 'gastos' => 0.0, 'neta' => 0.0, 'margen' => null, 'gastos_tipo' => [], 'sin_costo' => 0];

	$r = @$conn->query("SELECT COALESCE(SUM(dv.total + 0), 0) AS ventas,
			COALESCE(SUM((dv.cantidad + 0) * COALESCE($costo_expr, 0)), 0) AS costo,
			COALESCE(SUM(CASE WHEN $costo_expr IS NULL THEN 1 ELSE 0 END), 0) AS sin_costo
		FROM detalle_ventas dv
		INNER JOIN ventas v ON v.id_venta = dv.venta
		LEFT JOIN productos p ON p.id_producto = dv.producto
		WHERE v.estado != '2' AND v.fecha BETWEEN '$ini' AND '$fin'");
	if ($r && ($f = $r->fetch_assoc())) {
		$out['ventas'] = round((float)$f['ventas'], 2);
		$out['costo'] = round((float)$f['costo'], 2);
		$out['sin_costo'] = (int)$f['sin_costo'];
	}

	$r = @$conn->query("SELECT categoria, SUM(monto) AS monto FROM gastos
		WHERE estado = '1' AND fecha BETWEEN '$ini' AND '$fin' AND id_compra IS NULL
		GROUP BY categoria ORDER BY monto DESC");
	if (!$r) { // base sin la migración 05: sin distinguir fletes de compra
		$r = @$conn->query("SELECT categoria, SUM(monto) AS monto FROM gastos WHERE estado = '1' AND fecha BETWEEN '$ini' AND '$fin' GROUP BY categoria ORDER BY monto DESC");
	}
	while ($r && ($f = $r->fetch_assoc())) {
		$m = round((float)$f['monto'], 2);
		$out['gastos'] += $m;
		$out['gastos_tipo'][] = ['categoria' => $f['categoria'], 'monto' => $m];
	}
	$out['gastos'] = round($out['gastos'], 2);
	$out['bruta'] = round($out['ventas'] - $out['costo'], 2);
	$out['neta'] = round($out['bruta'] - $out['gastos'], 2);
	$out['margen'] = $out['ventas'] > 0 ? round($out['neta'] / $out['ventas'] * 100, 1) : null;
	return $out;
}
