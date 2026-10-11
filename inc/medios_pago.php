<?php
// Medios de pago: se administran en Configuración → Medios de pago (tabla medios_pago).
// Se usan al cobrar ventas en Caja, al pagar gastos/compras y para llevar el saldo por medio en el Balance.
require_once __DIR__ . '/config.php';

// Lista completa (incluye desactivados, para poder mostrar el nombre de pagos antiguos).
function medios_todos($recargar = false) {
	static $lista = null;
	if ($lista !== null && !$recargar) return $lista;
	$lista = [];
	$db = db_connect();
	if (!$db->connect_errno) {
		$r = @$db->query("SELECT clave, nombre, icono, digital, selector, sistema, orden, activo FROM medios_pago ORDER BY orden, nombre");
		while ($r && ($f = $r->fetch_assoc())) { $lista[$f['clave']] = $f; }
		$db->close();
	}
	if (!$lista) { // base sin la tabla nueva
		foreach ([['efectivo','Efectivo',0],['yape','Yape',1],['plin','Plin',1],['transferencia','Transferencia',1],['tarjeta','Tarjeta',0]] as $i => $m) {
			$lista[$m[0]] = ['clave'=>$m[0],'nombre'=>$m[1],'icono'=>'fa-money-bill-wave','digital'=>$m[2],'selector'=>1,'sistema'=>0,'orden'=>$i,'activo'=>1];
		}
	}
	return $lista;
}

// Medios que se pueden elegir al cobrar o pagar: clave => nombre.
function medios_seleccionables() {
	$out = [];
	foreach (medios_todos() as $k => $m) { if ($m['activo'] && $m['selector']) $out[$k] = $m['nombre']; }
	return $out;
}

// Claves de los medios electrónicos (para el indicador de pagos digitales del Escritorio).
function medios_digitales() {
	$out = [];
	foreach (medios_todos() as $k => $m) { if ($m['digital']) $out[] = $k; }
	return $out;
}

$MEDIOS_PAGO = medios_seleccionables();

// Valores antiguos de compra_pagos (antes de tener esta lista). Siguen siendo
// válidos al leer datos viejos, pero ya no se ofrecen al registrar.
$MEDIOS_LEGACY = ['deposito' => 'Depósito', 'cheque' => 'Cheque', 'otro' => 'Otro'];

function medio_label($k) {
	global $MEDIOS_LEGACY;
	$todos = medios_todos();
	return $todos[$k]['nombre'] ?? $MEDIOS_LEGACY[$k] ?? ucfirst(str_replace('_', ' ', $k));
}

function medios_options($sel = 'efectivo') {
	$h = '';
	foreach (medios_seleccionables() as $k => $v) $h .= '<option value="' . htmlspecialchars($k) . '"' . ($k === $sel ? ' selected' : '') . '>' . htmlspecialchars($v) . '</option>';
	return $h;
}
