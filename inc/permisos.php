<?php
// Módulos activos y permisos por rol.
// Si las tablas de sql/01_modulos_permisos.sql aún no existen, se usa el comportamiento anterior
// (admin ve todo; el resto solo Escritorio, Ventas y Caja).

require_once __DIR__ . '/config.php';

function permisos_db() {
	static $db = false;
	if ($db === false) {
		$db = @db_connect();
		if ($db->connect_errno) { $db = null; }
		else { $db->set_charset('utf8'); }
	}
	return $db;
}

// Carga una sola vez por request: catálogo, rol del usuario y su matriz de permisos.
function permisos_cargar() {
	static $c = null;
	if ($c !== null) return $c;
	$c = ['legacy' => true, 'admin' => false, 'modulos' => [], 'perm' => []];
	$es_admin_legacy = (($_SESSION['type'] ?? '') === 'admin');
	$c['admin'] = $es_admin_legacy;

	$db = permisos_db();
	$res = $db ? $db->query("SELECT * FROM modulos ORDER BY orden, id_modulo") : false;
	if (!$res) {
		// Sin tablas nuevas: comportamiento anterior.
		foreach (['dashboard', 'ventas', 'caja'] as $k) { $c['perm'][$k] = ['ver'=>1,'crear'=>1,'editar'=>1,'anular'=>1]; }
		return $c;
	}
	$c['legacy'] = false;
	while ($m = $res->fetch_assoc()) { $c['modulos'][$m['clave']] = $m; }

	$uid = (int)($_SESSION['id_usr'] ?? 0);
	$rol_id = 0;
	$ur = $db->query("SELECT u.rol_id, COALESCE(r.es_admin,0) AS es_admin FROM usuarios u LEFT JOIN roles r ON r.id_rol = u.rol_id WHERE u.id_usuario = $uid");
	if ($ur && ($u = $ur->fetch_assoc())) {
		$rol_id = (int)$u['rol_id'];
		$c['admin'] = $rol_id ? (bool)$u['es_admin'] : $es_admin_legacy;
	}
	if ($rol_id && !$c['admin']) {
		$pr = $db->query("SELECT m.clave, p.ver, p.crear, p.editar, p.anular FROM rol_permisos p JOIN modulos m ON m.id_modulo = p.id_modulo WHERE p.id_rol = $rol_id");
		while ($pr && ($p = $pr->fetch_assoc())) {
			$c['perm'][$p['clave']] = ['ver'=>(int)$p['ver'],'crear'=>(int)$p['crear'],'editar'=>(int)$p['editar'],'anular'=>(int)$p['anular']];
		}
	}
	return $c;
}

// Módulo activo = encendido y con todas sus dependencias activas.
function modulo_activo($clave, $_visto = []) {
	$c = permisos_cargar();
	if ($c['legacy']) return true;
	$m = $c['modulos'][$clave] ?? null;
	if (!$m || in_array($clave, $_visto, true)) return false;
	if (!$m['nucleo'] && !$m['activo']) return false;
	if (!empty($m['depende'])) {
		foreach (explode(',', $m['depende']) as $dep) {
			$dep = trim($dep);
			if ($dep !== '' && !modulo_activo($dep, array_merge($_visto, [$clave]))) return false;
		}
	}
	return true;
}

// $accion: ver | crear | editar | anular
function puede($clave, $accion = 'ver') {
	$c = permisos_cargar();
	if (!modulo_activo($clave)) return false;
	if ($c['admin']) return true;
	return !empty($c['perm'][$clave]['ver']) && !empty($c['perm'][$clave][$accion]);
}

// Módulos que se muestran a este usuario en la barra superior.
// $solo_menu = true: los de la barra; false: los que van en el desplegable Config.
function modulos_para_menu($solo_menu = true) {
	$c = permisos_cargar();
	$out = [];
	if ($c['legacy']) {
		$base = [
			['dashboard','Escritorio','dashboard.php','ph-house',37,1],
			['usuarios','Usuarios','ver_usuarios.php','ph-user-plus',145,1],
			['clientes','Clientes','ver_clientes.php','ph-users-three',60,1],
			['productos','Productos','ver_productos.php','ph-package',75,1],
			['ventas','Ventas','venta.php','ph-receipt',230,1],
			['caja','Caja','caja_pagos.php','ph-cash-register',150,1],
			['compras','Compras','compra.php','ph-shopping-cart',170,1],
			['preventas','Preventas','preventas.php','ph-clipboard-text',30,1],
			['gastos','Gastos','gastos.php','ph-wallet',120,1],
			['reportes','Reportes','reportes.php','ph-chart-line-up',250,1],
		];
		if (!$solo_menu) return $out;
		foreach ($base as $b) {
			if (!$c['admin'] && !in_array($b[0], ['dashboard','ventas','caja'])) continue;
			$out[] = ['clave'=>$b[0],'nombre'=>$b[1],'url'=>$b[2],'icono'=>$b[3],'tono'=>$b[4]];
		}
		return $out;
	}
	foreach ($c['modulos'] as $clave => $m) {
		if ((bool)$m['menu'] !== $solo_menu) continue;
		if (!puede($clave, 'ver')) continue;
		$out[] = ['clave'=>$clave,'nombre'=>$m['nombre'],'url'=>$m['url'],'icono'=>$m['icono'],'tono'=>(int)$m['tono']];
	}
	return $out;
}

// Enlaces extra del desplegable Config., cada uno gobernado por su módulo.
function config_enlaces() {
	$extras = [
		['configuracion', 'modulos_config.php',            'Módulos y permisos',     'ph-shield-check'],
		['tablet',        'tablet_config_admin.php',       'Configuración Tablet',   'ph-device-tablet'],
		['facturacion',   'configuracion_facturacion.php', 'Facturación Electrónica','ph-file-text'],
		['productos',     'alias_productos.php',           'Diccionario',            'ph-book-open'],
		['planillas',     'configuracion_planillas.php',   'Configuración Planillas','ph-sliders'],
		['proveedores',   'proveedores.php',               'Proveedores',            'ph-truck'],
		['auditoria',     'logs_auditoria.php',            'Auditoría',              'ph-clock-counter-clockwise'],
	];
	$c = permisos_cargar();
	$out = [];
	foreach ($extras as $e) {
		if ($c['legacy']) {
			// Solo lo que el menú original ofrecía al admin.
			if ($c['admin'] && in_array($e[1], ['tablet_config_admin.php','configuracion_facturacion.php','alias_productos.php'])) $out[] = $e;
			continue;
		}
		if (puede($e[0], 'ver')) $out[] = $e;
	}
	return $out;
}

function permisos_accion_de_archivo($archivo) {
	if (preg_match('/^(agregar_|registrar_|generar_)/', $archivo)) return 'crear';
	if (preg_match('/^editar_/', $archivo)) return 'editar';
	if (preg_match('/^(anular_|borrar_)/', $archivo)) return 'anular';
	return 'ver';
}

function permisos_modulo_de_archivo($archivo) {
	static $mapa = null;
	if ($mapa === null) {
		$mapa = [];
		foreach (require __DIR__ . '/permisos_paginas.php' as $clave => $paginas) {
			foreach ($paginas as $p) { $mapa[$p] = $clave; }
		}
	}
	return $mapa[$archivo] ?? null;
}

function permisos_denegar() {
	$ajax = (strtolower($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'xmlhttprequest')
		|| strpos($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json') !== false;
	if ($ajax) {
		http_response_code(403);
		header('Content-Type: application/json');
		echo json_encode(['respuesta' => false, 'mensaje' => 'No tienes permiso para esta acción']);
		exit;
	}
	// Primera pantalla a la que sí tiene acceso (evita bucles de redirección).
	$destino = null;
	foreach (modulos_para_menu(true) as $m) { $destino = $m['url']; break; }
	if ($destino && $destino !== basename($_SERVER['SCRIPT_NAME'])) {
		header('Location: ' . $destino);
		exit;
	}
	http_response_code(403);
	echo '<!DOCTYPE html><meta charset="utf-8"><title>Sin acceso</title><p style="font-family:sans-serif;padding:40px">No tienes acceso a este módulo. Consulta con el administrador. <a href="salir.php">Salir</a></p>';
	exit;
}

// Guarda un módulo/acción concreto (para usar dentro de una página o endpoint).
function requiere_modulo($clave, $accion = 'ver') {
	if (!puede($clave, $accion)) permisos_denegar();
}

// Guardia automática por nombre de archivo; la invoca inc/control.php.
function requiere_pagina() {
	$archivo = basename($_SERVER['SCRIPT_NAME'] ?? '');
	$clave = permisos_modulo_de_archivo($archivo);
	if ($clave === null) return;
	requiere_modulo($clave, permisos_accion_de_archivo($archivo));
}
