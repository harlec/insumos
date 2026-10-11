<?php
// API de la pantalla Módulos y permisos (JSON). Requiere el módulo 'configuracion'.
session_start();
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/permisos.php';
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
$db = permisos_db();
if (!$db) salir(false, ['mensaje' => 'Sin conexión a la base'], 500);
$db->set_charset('utf8');

function datos($db) {
	permisos_cargar(true);
	$mod = []; $r = $db->query("SELECT * FROM modulos ORDER BY orden, id_modulo");
	while ($f = $r->fetch_assoc()) {
		$f['id_modulo'] = (int)$f['id_modulo']; $f['activo'] = (int)$f['activo']; $f['nucleo'] = (int)$f['nucleo'];
		$f['efectivo'] = modulo_activo($f['clave']) ? 1 : 0;
		$mod[] = $f;
	}
	$roles = []; $r = $db->query("SELECT r.*, (SELECT COUNT(*) FROM usuarios u WHERE u.rol_id = r.id_rol) AS usuarios FROM roles r WHERE r.estado = 1 ORDER BY r.es_admin DESC, r.nombre");
	while ($f = $r->fetch_assoc()) {
		$f['id_rol'] = (int)$f['id_rol']; $f['es_admin'] = (int)$f['es_admin']; $f['usuarios'] = (int)$f['usuarios'];
		$f['permisos'] = new stdClass();
		$roles[$f['id_rol']] = $f;
	}
	$r = $db->query("SELECT p.id_rol, m.clave, p.ver, p.crear, p.editar, p.anular FROM rol_permisos p JOIN modulos m ON m.id_modulo = p.id_modulo");
	while ($f = $r->fetch_assoc()) {
		if (!isset($roles[(int)$f['id_rol']])) continue;
		$roles[(int)$f['id_rol']]['permisos']->{$f['clave']} = ['ver'=>(int)$f['ver'],'crear'=>(int)$f['crear'],'editar'=>(int)$f['editar'],'anular'=>(int)$f['anular']];
	}
	return ['modulos' => $mod, 'roles' => array_values($roles)];
}

// Claves de las que depende $clave, de forma recursiva.
function dependencias($db, $clave, &$vistas = []) {
	if (isset($vistas[$clave])) return [];
	$vistas[$clave] = true;
	$st = $db->prepare("SELECT depende FROM modulos WHERE clave = ?");
	$st->bind_param('s', $clave); $st->execute();
	$f = $st->get_result()->fetch_assoc();
	$out = [];
	foreach (array_filter(array_map('trim', explode(',', $f['depende'] ?? ''))) as $d) {
		$out[] = $d;
		$out = array_merge($out, dependencias($db, $d, $vistas));
	}
	return array_values(array_unique($out));
}

switch ($accion) {
	case 'listar':
		salir(true, datos($db));

	case 'modulo_activo':
		$id = (int)($_POST['id'] ?? 0); $activo = !empty($_POST['activo']) ? 1 : 0;
		$st = $db->prepare("SELECT clave, nombre, nucleo FROM modulos WHERE id_modulo = ?");
		$st->bind_param('i', $id); $st->execute();
		$m = $st->get_result()->fetch_assoc();
		if (!$m) salir(false, ['mensaje' => 'Módulo no existe'], 404);
		if ($m['nucleo'] && !$activo) salir(false, ['mensaje' => 'Este módulo no se puede apagar']);
		$extra = [];
		$st = $db->prepare("UPDATE modulos SET activo = ? WHERE id_modulo = ?");
		$st->bind_param('ii', $activo, $id); $st->execute();
		if ($activo) {
			foreach (dependencias($db, $m['clave']) as $dep) {
				$st2 = $db->prepare("UPDATE modulos SET activo = 1 WHERE clave = ? AND activo = 0");
				$st2->bind_param('s', $dep); $st2->execute();
				if ($st2->affected_rows) $extra[] = $dep;
			}
		}
		salir(true, array_merge(datos($db), ['tambien_activados' => $extra]));

	case 'guardar_rol':
		$id = (int)($_POST['id'] ?? 0);
		$nombre = trim($_POST['nombre'] ?? ''); $desc = trim($_POST['descripcion'] ?? '');
		$perm = json_decode($_POST['permisos'] ?? '{}', true) ?: [];
		if ($nombre === '' || mb_strlen($nombre) > 40) salir(false, ['mensaje' => 'Nombre obligatorio (máx. 40 caracteres)']);
		if ($id) {
			$st = $db->prepare("SELECT es_admin FROM roles WHERE id_rol = ? AND estado = 1");
			$st->bind_param('i', $id); $st->execute();
			$r0 = $st->get_result()->fetch_assoc();
			if (!$r0) salir(false, ['mensaje' => 'Rol no existe'], 404);
			if ($r0['es_admin']) salir(false, ['mensaje' => 'El rol Administrador no se edita']);
			$st = $db->prepare("UPDATE roles SET nombre = ?, descripcion = ? WHERE id_rol = ?");
			$st->bind_param('ssi', $nombre, $desc, $id);
		} else {
			$st = $db->prepare("INSERT INTO roles (nombre, descripcion) VALUES (?, ?)");
			$st->bind_param('ss', $nombre, $desc);
		}
		if (!$st->execute()) salir(false, ['mensaje' => $db->errno == 1062 ? 'Ya existe un rol con ese nombre' : 'No se pudo guardar']);
		if (!$id) $id = $db->insert_id;
		$db->begin_transaction();
		$db->query("DELETE FROM rol_permisos WHERE id_rol = " . (int)$id);
		$ins = $db->prepare("INSERT INTO rol_permisos (id_rol, id_modulo, ver, crear, editar, anular) VALUES (?, ?, ?, ?, ?, ?)");
		$r = $db->query("SELECT id_modulo, clave FROM modulos");
		while ($m = $r->fetch_assoc()) {
			$p = $perm[$m['clave']] ?? null;
			if (!$p || empty($p['ver'])) continue; // sin "ver" no hay acceso ni acciones
			$ver = 1; $cr = !empty($p['crear']) ? 1 : 0; $ed = !empty($p['editar']) ? 1 : 0; $an = !empty($p['anular']) ? 1 : 0;
			$idm = (int)$m['id_modulo'];
			$ins->bind_param('iiiiii', $id, $idm, $ver, $cr, $ed, $an); $ins->execute();
		}
		$db->commit();
		salir(true, array_merge(datos($db), ['id' => $id]));

	case 'borrar_rol':
		$id = (int)($_POST['id'] ?? 0);
		$st = $db->prepare("SELECT es_admin, (SELECT COUNT(*) FROM usuarios WHERE rol_id = ?) AS n FROM roles WHERE id_rol = ?");
		$st->bind_param('ii', $id, $id); $st->execute();
		$r0 = $st->get_result()->fetch_assoc();
		if (!$r0) salir(false, ['mensaje' => 'Rol no existe'], 404);
		if ($r0['es_admin']) salir(false, ['mensaje' => 'El rol Administrador no se puede borrar']);
		if ($r0['n'] > 0) salir(false, ['mensaje' => 'Hay ' . $r0['n'] . ' usuario(s) con este rol. Cámbialos de rol primero.']);
		$st = $db->prepare("DELETE FROM roles WHERE id_rol = ?");
		$st->bind_param('i', $id); $st->execute();
		salir(true, datos($db));

	default:
		salir(false, ['mensaje' => 'Acción desconocida'], 400);
}
