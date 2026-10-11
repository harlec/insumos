<?php
// Configuración central: credenciales de BD y secreto de claves.
// Lee config/config.php (generado por instalar.php, fuera de git).

function cfg($ruta = null, $defecto = null) {
	static $c = null;
	if ($c === null) {
		$f = dirname(__DIR__) . '/config/config.php';
		$c = is_file($f) ? (require $f) : [];
	}
	if ($ruta === null) return $c;
	$v = $c;
	foreach (explode('.', $ruta) as $k) {
		if (!is_array($v) || !array_key_exists($k, $v)) return $defecto;
		$v = $v[$k];
	}
	return $v;
}

function app_instalada() {
	return is_file(dirname(__DIR__) . '/config/config.php') && is_file(dirname(__DIR__) . '/config/instalado.lock');
}

// Conexión mysqli con las credenciales configuradas (sustituye a los new mysqli(...) con datos escritos a mano).
function db_connect() {
	mysqli_report(MYSQLI_REPORT_OFF);
	return new mysqli(cfg('db.host', 'localhost'), cfg('db.user', ''), cfg('db.pass', ''), cfg('db.name', ''), (int)cfg('db.port', 3306));
}

// Hash de clave de usuario; debe coincidir en login, alta y edición de usuarios.
function hash_clave($clave) {
	return hash_hmac('sha256', $clave, cfg('pepper', ''));
}

// Si el sistema no está instalado, lleva al instalador (o responde JSON a llamadas AJAX).
if (PHP_SAPI !== 'cli' && !app_instalada() && basename($_SERVER['SCRIPT_NAME'] ?? '') !== 'instalar.php') {
	$ajax = (strtolower($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'xmlhttprequest')
		|| strpos($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json') !== false
		|| basename(dirname($_SERVER['SCRIPT_NAME'] ?? '')) === 'inc';
	if ($ajax) {
		header('Content-Type: application/json');
		echo json_encode(['respuesta' => false, 'mensaje' => 'Sistema no instalado: abre /instalar.php']);
	} else {
		header('Location: /instalar.php');
	}
	exit;
}
