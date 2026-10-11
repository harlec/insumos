<?php
// Datos de la empresa y marca (editables en Configuración → Datos de la empresa).
// Se guardan en la tabla `configuracion` (parámetros empresa_* y marca_*).
require_once __DIR__ . '/config.php';

function empresa_defaults() {
	return [
		'empresa_nombre'    => 'Mi Empresa',
		'empresa_razon'     => '',
		'empresa_ruc'       => '',
		'empresa_direccion' => '',
		'empresa_telefono'  => '',
		'empresa_email'     => '',
		'empresa_lema'      => '',
		'empresa_gracias'   => 'Gracias por su preferencia',
		'marca_modo'        => 'texto',   // texto | imagen
		'marca_linea1'      => '',
		'marca_linea2'      => '',
		'marca_logo'        => '',        // ruta relativa a la raíz, p. ej. assets/uploads/logo_ab12.png
	];
}

function empresa_todos($recargar = false) {
	static $datos = null;
	if ($datos !== null && !$recargar) return $datos;
	$datos = empresa_defaults();
	$db = db_connect();
	if (!$db->connect_errno) {
		$r = @$db->query("SELECT parametro, valor FROM configuracion WHERE parametro LIKE 'empresa\\_%' OR parametro LIKE 'marca\\_%'");
		while ($r && ($f = $r->fetch_assoc())) {
			if (array_key_exists($f['parametro'], $datos)) $datos[$f['parametro']] = (string)$f['valor'];
		}
		$db->close();
	}
	// Sin texto de marca definido, se usa el nombre de la empresa.
	if ($datos['marca_linea1'] === '' && $datos['marca_linea2'] === '') $datos['marca_linea2'] = $datos['empresa_nombre'];
	return $datos;
}

function empresa($clave, $defecto = '') {
	$d = empresa_todos();
	return ($d[$clave] ?? '') !== '' ? $d[$clave] : $defecto;
}

// Ruta en disco del logo subido (o null).
function empresa_logo_ruta() {
	$rel = empresa_todos()['marca_logo'];
	if ($rel === '' || !preg_match('#^assets/uploads/[A-Za-z0-9_.-]+$#', $rel)) return null;
	$ruta = dirname(__DIR__) . '/' . $rel;
	return is_file($ruta) ? $ruta : null;
}

function empresa_logo_url() {
	$ruta = empresa_logo_ruta();
	return $ruta ? '/' . empresa_todos()['marca_logo'] . '?v=' . filemtime($ruta) : null;
}

// Logo como data URI (para dompdf y tickets que no cargan URLs).
function empresa_logo_data_uri() {
	$ruta = empresa_logo_ruta();
	if (!$ruta) return null;
	$mime = @mime_content_type($ruta) ?: 'image/png';
	return 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($ruta));
}

function empresa_h($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }

// Contenido de la marca para la barra superior y el login: imagen o dos líneas de texto.
function marca_html() {
	$d = empresa_todos();
	if ($d['marca_modo'] === 'imagen' && ($url = empresa_logo_url())) {
		return '<img class="esc-brand-img" src="' . empresa_h($url) . '" alt="' . empresa_h($d['empresa_nombre']) . '">';
	}
	$l1 = $d['marca_linea1']; $l2 = $d['marca_linea2'];
	return ($l1 !== '' ? '<span>' . empresa_h($l1) . '</span>' : '') . '<b>' . empresa_h($l2) . '</b>';
}

// Texto ASCII para tickets ESC/POS y PDF (sin acentos problemáticos se resuelve con iconv en el llamador).
function empresa_lineas_ticket() {
	$d = empresa_todos();
	$l = [$d['empresa_nombre']];
	if ($d['empresa_razon'] !== '' && $d['empresa_razon'] !== $d['empresa_nombre']) $l[] = $d['empresa_razon'];
	if ($d['empresa_ruc'] !== '') $l[] = 'RUC ' . $d['empresa_ruc'];
	if ($d['empresa_direccion'] !== '') $l[] = $d['empresa_direccion'];
	if ($d['empresa_telefono'] !== '') $l[] = 'Tel: ' . $d['empresa_telefono'];
	return $l;
}

// Cabecera de recibos PDF (dompdf): logo o nombre, datos de la empresa y frase opcional.
function empresa_cabecera_html($ancho_logo = 200) {
	$d = empresa_todos();
	$h = '';
	if (($uri = empresa_logo_data_uri())) {
		// dompdf necesita ancho y alto explícitos: se ajusta el logo a un máximo de $ancho_logo x 64 px.
		$dim = @getimagesize(empresa_logo_ruta());
		$w = $ancho_logo; $hh = 64;
		if ($dim && $dim[0] > 0 && $dim[1] > 0) {
			$esc = min($ancho_logo / $dim[0], 64 / $dim[1], 1);
			$w = max(1, (int)round($dim[0] * $esc)); $hh = max(1, (int)round($dim[1] * $esc));
		}
		$h .= '<div style="text-align:center;margin:0 0 4px 0"><img width="' . $w . '" height="' . $hh . '" src="' . $uri . '"></div>';
	} else {
		$h .= '<h4 style="text-align:center;margin:2px 0"><b>' . empresa_h(mb_strtoupper($d['empresa_nombre'])) . '</b></h4>';
	}
	$lineas = [];
	if ($d['empresa_razon'] !== '' && $d['empresa_razon'] !== $d['empresa_nombre']) $lineas[] = '<b>' . empresa_h($d['empresa_razon']) . '</b>';
	if ($d['empresa_ruc'] !== '') $lineas[] = 'RUC ' . empresa_h($d['empresa_ruc']);
	if ($d['empresa_direccion'] !== '') $lineas[] = empresa_h($d['empresa_direccion']);
	$contacto = array_filter([$d['empresa_telefono'] !== '' ? 'Tel: ' . empresa_h($d['empresa_telefono']) : '', empresa_h($d['empresa_email'])]);
	if ($contacto) $lineas[] = implode(' - ', $contacto);
	if ($lineas) $h .= '<h6 style="text-align:center;margin:2px 0">' . implode('<br>', $lineas) . '</h6>';
	if ($d['empresa_lema'] !== '') $h .= '<h6 style="text-align:center;margin:2px 0"><i>' . empresa_h($d['empresa_lema']) . '</i></h6>';
	return $h;
}
