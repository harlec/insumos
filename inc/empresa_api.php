<?php
// API de Configuración → Datos de la empresa y Medios de pago (JSON). Requiere el módulo 'configuracion'.
session_start();
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/permisos.php';
require_once __DIR__ . '/empresa.php';
require_once __DIR__ . '/medios_pago.php';
header('Content-Type: application/json; charset=utf-8');

function salir($ok, $datos = [], $codigo = 200) {
	http_response_code($codigo);
	echo json_encode(array_merge(['respuesta' => $ok], $datos));
	exit;
}

if (empty($_SESSION['ingress'])) salir(false, ['mensaje' => 'Sesión expirada'], 401);
$accion = $_POST['accion'] ?? $_GET['accion'] ?? 'listar';
$escribe = $accion !== 'listar';
if (!puede('configuracion', $escribe ? 'editar' : 'ver')) salir(false, ['mensaje' => 'No tienes permiso'], 403);
if ($escribe) {
	$tok = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
	if (empty($_SESSION['csrf']) || !hash_equals($_SESSION['csrf'], $tok)) salir(false, ['mensaje' => 'Token inválido, recarga la página'], 403);
}
$db = db_connect();
if ($db->connect_errno) salir(false, ['mensaje' => 'Sin conexión a la base'], 500);
$db->set_charset('utf8');

function cfg_guardar($db, $parametro, $valor) {
	$st = $db->prepare("SELECT id_parametro FROM configuracion WHERE parametro = ? LIMIT 1");
	$st->bind_param('s', $parametro); $st->execute();
	if ($st->get_result()->fetch_row()) {
		$st = $db->prepare("UPDATE configuracion SET valor = ? WHERE parametro = ?");
		$st->bind_param('ss', $valor, $parametro);
	} else {
		$st = $db->prepare("INSERT INTO configuracion (parametro, valor, estado_parametro) VALUES (?, ?, '1')");
		$st->bind_param('ss', $parametro, $valor);
	}
	return $st->execute();
}

function estado($db) {
	$d = empresa_todos(true);
	$medios = [];
	foreach (medios_todos(true) as $m) {
		foreach (['digital','selector','sistema','activo'] as $c) $m[$c] = (int)$m[$c];
		$medios[] = $m;
	}
	return ['empresa' => $d, 'logo_url' => empresa_logo_url(), 'medios' => $medios];
}

$limites = ['empresa_nombre'=>80,'empresa_razon'=>120,'empresa_ruc'=>15,'empresa_direccion'=>160,'empresa_telefono'=>40,'empresa_email'=>80,'empresa_lema'=>160,'empresa_gracias'=>120,'marca_linea1'=>30,'marca_linea2'=>30];

switch ($accion) {
	case 'listar':
		salir(true, estado($db));

	case 'guardar_empresa':
		$modo = ($_POST['marca_modo'] ?? 'texto') === 'imagen' ? 'imagen' : 'texto';
		if (trim($_POST['empresa_nombre'] ?? '') === '') salir(false, ['mensaje' => 'El nombre de la empresa es obligatorio']);
		foreach ($limites as $k => $max) {
			$v = trim($_POST[$k] ?? '');
			cfg_guardar($db, $k, mb_substr(preg_replace('/\s+/', ' ', $v), 0, $max));
		}
		cfg_guardar($db, 'marca_modo', $modo);
		salir(true, array_merge(estado($db), ['mensaje' => 'Datos guardados']));

	case 'subir_logo':
		$f = $_FILES['logo'] ?? null;
		if (!$f || $f['error'] !== UPLOAD_ERR_OK) salir(false, ['mensaje' => 'No se pudo recibir la imagen']);
		if ($f['size'] > 1500000) salir(false, ['mensaje' => 'La imagen pesa más de 1.5 MB']);
		$info = @getimagesize($f['tmp_name']);
		$tipos = [IMAGETYPE_PNG => 'png', IMAGETYPE_JPEG => 'jpg', IMAGETYPE_WEBP => 'webp'];
		if (!$info || !isset($tipos[$info[2]])) salir(false, ['mensaje' => 'Usa una imagen PNG, JPG o WEBP']);
		if ($info[0] > 3000 || $info[1] > 3000) salir(false, ['mensaje' => 'La imagen es demasiado grande (máx. 3000 px)']);
		$dir = dirname(__DIR__) . '/assets/uploads';
		if (!is_dir($dir) && !@mkdir($dir, 0755, true)) salir(false, ['mensaje' => 'No se pudo crear la carpeta assets/uploads (revisa permisos)'], 500);
		$nombre = 'logo_' . bin2hex(random_bytes(6)) . '.' . $tipos[$info[2]];
		if (!move_uploaded_file($f['tmp_name'], $dir . '/' . $nombre)) salir(false, ['mensaje' => 'No se pudo guardar la imagen (revisa permisos de assets/uploads)'], 500);
		@chmod($dir . '/' . $nombre, 0644);
		// borra el logo anterior
		$ant = empresa_todos(true)['marca_logo'];
		if ($ant !== '' && preg_match('#^assets/uploads/logo_[A-Za-z0-9_.]+$#', $ant)) @unlink(dirname(__DIR__) . '/' . $ant);
		cfg_guardar($db, 'marca_logo', 'assets/uploads/' . $nombre);
		cfg_guardar($db, 'marca_modo', 'imagen');
		salir(true, array_merge(estado($db), ['mensaje' => 'Logo actualizado']));

	case 'quitar_logo':
		$ant = empresa_todos(true)['marca_logo'];
		if ($ant !== '' && preg_match('#^assets/uploads/logo_[A-Za-z0-9_.]+$#', $ant)) @unlink(dirname(__DIR__) . '/' . $ant);
		cfg_guardar($db, 'marca_logo', '');
		cfg_guardar($db, 'marca_modo', 'texto');
		salir(true, array_merge(estado($db), ['mensaje' => 'Logo quitado']));

	case 'medio_guardar':
		$clave = trim($_POST['clave'] ?? '');
		$nombre = mb_substr(trim($_POST['nombre'] ?? ''), 0, 40);
		$icono = $_POST['icono'] ?? 'fa-money-bill-wave';
		$iconos = ['fa-money-bill-wave','fa-mobile-alt','fa-university','fa-credit-card','fa-wallet','fa-coins','fa-qrcode','fa-exchange-alt','fa-hand-holding-usd','fa-file-invoice'];
		if (!in_array($icono, $iconos, true)) $icono = 'fa-money-bill-wave';
		$digital = !empty($_POST['digital']) ? 1 : 0;
		$activo = !empty($_POST['activo']) ? 1 : 0;
		if ($nombre === '') salir(false, ['mensaje' => 'Escribe el nombre del medio de pago']);
		if ($clave === '') {
			// clave nueva: nombre en minúsculas sin acentos ni espacios
			$sin_tildes = strtr($nombre, ['á'=>'a','é'=>'e','í'=>'i','ó'=>'o','ú'=>'u','ü'=>'u','ñ'=>'n','Á'=>'A','É'=>'E','Í'=>'I','Ó'=>'O','Ú'=>'U','Ü'=>'U','Ñ'=>'N']);
			$ascii = @iconv('UTF-8', 'ASCII//TRANSLIT', $sin_tildes);
			$base = trim(preg_replace('/[^a-z0-9]+/', '_', strtolower($ascii !== false ? $ascii : $nombre)), '_');
			$clave = substr($base !== '' ? $base : 'medio', 0, 24);
			$n = 1; $c = $clave;
			while (true) {
				$st = $db->prepare("SELECT 1 FROM medios_pago WHERE clave = ?"); $st->bind_param('s', $c); $st->execute();
				if (!$st->get_result()->fetch_row()) break;
				$c = $clave . '_' . (++$n);
			}
			$st = $db->prepare("SELECT COALESCE(MAX(orden),0)+10 FROM medios_pago WHERE sistema = 0 AND orden < 900");
			$st->execute(); $orden = (int)$st->get_result()->fetch_row()[0];
			$st = $db->prepare("INSERT INTO medios_pago (clave, nombre, icono, digital, selector, sistema, orden, activo) VALUES (?, ?, ?, ?, 1, 0, ?, ?)");
			$st->bind_param('sssiii', $c, $nombre, $icono, $digital, $orden, $activo);
		} else {
			$st = $db->prepare("SELECT sistema FROM medios_pago WHERE clave = ?"); $st->bind_param('s', $clave); $st->execute();
			$m = $st->get_result()->fetch_assoc();
			if (!$m) salir(false, ['mensaje' => 'Medio de pago no existe'], 404);
			if ($m['sistema']) $activo = 1; // efectivo y planilla no se desactivan
			$st = $db->prepare("UPDATE medios_pago SET nombre = ?, icono = ?, digital = ?, activo = ? WHERE clave = ?");
			$st->bind_param('ssiis', $nombre, $icono, $digital, $activo, $clave);
		}
		if (!$st->execute()) salir(false, ['mensaje' => 'No se pudo guardar']);
		salir(true, array_merge(estado($db), ['mensaje' => 'Medio de pago guardado']));

	case 'medio_borrar':
		$clave = trim($_POST['clave'] ?? '');
		$st = $db->prepare("SELECT sistema FROM medios_pago WHERE clave = ?"); $st->bind_param('s', $clave); $st->execute();
		$m = $st->get_result()->fetch_assoc();
		if (!$m) salir(false, ['mensaje' => 'Medio de pago no existe'], 404);
		if ($m['sistema']) salir(false, ['mensaje' => 'Este medio es del sistema y no se puede borrar']);
		$uso = 0;
		foreach (['venta_pagos', 'compra_pagos', 'gastos'] as $t) {
			$col = $t === 'gastos' ? 'metodo' : 'metodo';
			$st = $db->prepare("SELECT COUNT(*) FROM `$t` WHERE `$col` = ?"); $st->bind_param('s', $clave); $st->execute();
			$uso += (int)$st->get_result()->fetch_row()[0];
		}
		if ($uso > 0) salir(false, ['mensaje' => "Ya tiene $uso movimiento(s) registrados. Desactívalo en lugar de borrarlo."]);
		$st = $db->prepare("DELETE FROM medios_pago WHERE clave = ?"); $st->bind_param('s', $clave); $st->execute();
		salir(true, array_merge(estado($db), ['mensaje' => 'Medio de pago borrado']));

	default:
		salir(false, ['mensaje' => 'Acción desconocida'], 400);
}
