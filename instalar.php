<?php
// Instalador: crea las tablas, el primer administrador y config/config.php.
// Se bloquea solo cuando termina (config/instalado.lock).
require_once __DIR__ . '/inc/config.php';
require_once __DIR__ . '/inc/migrar.php';

$raiz   = __DIR__;
$dirCfg = $raiz . '/config';
$dirSql = $raiz . '/sql';

function h($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }

$instalada = app_instalada();
$errores   = [];
$ok        = null;
$v = ['host'=>'localhost','port'=>'3306','name'=>'admin_insumos','user'=>'','crear'=>'1','nombres'=>'','usuario'=>'','empresa'=>''];

// Requisitos previos
$req = [
	['PHP 7.4 o superior (tienes ' . PHP_VERSION . ')', version_compare(PHP_VERSION, '7.4.0', '>=')],
	['Extensión mysqli', extension_loaded('mysqli')],
	['Carpeta config/ con permiso de escritura', is_dir($dirCfg) && is_writable($dirCfg)],
	['Archivos sql/00_schema_insumos.sql y 01_modulos_permisos.sql', is_file($dirSql . '/00_schema_insumos.sql') && is_file($dirSql . '/01_modulos_permisos.sql')],
];
$reqOk = !in_array(false, array_column($req, 1), true);

if (!$instalada && $_SERVER['REQUEST_METHOD'] === 'POST') {
	foreach (['host','port','name','user','nombres','usuario','empresa'] as $k) { $v[$k] = trim($_POST[$k] ?? ''); }
	$v['crear'] = !empty($_POST['crear']) ? '1' : '';
	$passDb = $_POST['pass'] ?? '';
	$clave  = $_POST['clave'] ?? '';

	if (!$reqOk) $errores[] = 'Corrige los requisitos marcados antes de instalar.';
	if ($v['host'] === '' || $v['name'] === '' || $v['user'] === '') $errores[] = 'Completa servidor, base de datos y usuario de la base.';
	if (!preg_match('/^[A-Za-z0-9_]+$/', $v['name'])) $errores[] = 'El nombre de la base solo puede tener letras, números y guion bajo.';
	if ($v['empresa'] === '') $errores[] = 'Escribe el nombre de tu empresa.';
	if ($v['nombres'] === '' || $v['usuario'] === '') $errores[] = 'Indica nombre y usuario del administrador.';
	if (strlen($v['usuario']) > 15) $errores[] = 'El usuario admite hasta 15 caracteres.';
	if (strlen($clave) < 8) $errores[] = 'La clave del administrador debe tener al menos 8 caracteres.';
	if ($clave !== ($_POST['clave2'] ?? '')) $errores[] = 'Las claves no coinciden.';

	if (!$errores) {
		mysqli_report(MYSQLI_REPORT_OFF);
		$db = @new mysqli($v['host'], $v['user'], $passDb, '', (int)$v['port']);
		if ($db->connect_errno) {
			$errores[] = 'No se pudo conectar al servidor MySQL: ' . $db->connect_error;
		} else {
			$db->set_charset('utf8');
			if ($v['crear'] && !$db->query("CREATE DATABASE IF NOT EXISTS `{$v['name']}` CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci")) {
				$errores[] = 'No se pudo crear la base: ' . $db->error;
			}
			if (!$errores && !$db->select_db($v['name'])) {
				$errores[] = 'No existe la base "' . $v['name'] . '" o el usuario no tiene acceso. ' . $db->error;
			}
		}
		if (!$errores) {
			// La base debe estar vacía (solo se tolera schema_migraciones de un intento previo).
			$tablas = [];
			$r = $db->query('SHOW TABLES');
			while ($r && ($f = $r->fetch_row())) { $tablas[] = $f[0]; }
			if (array_diff($tablas, ['schema_migraciones'])) {
				$errores[] = 'La base "' . $v['name'] . '" ya tiene tablas. Usa una base vacía para instalar.';
			}
		}
		if (!$errores) {
			try {
				$aplicadas = migrar_aplicar($db, $dirSql);
				$pepper = bin2hex(random_bytes(16));
				$hash = hash_hmac('sha256', $clave, $pepper);
				$rol = $db->query("SELECT id_rol FROM roles WHERE nombre = 'Administrador'")->fetch_row();
				$rolId = $rol ? (int)$rol[0] : null;
				$st = $db->prepare("INSERT INTO usuarios (usuario, clave, rol, rol_id, nombres, tienda, estado, tablet_mode) VALUES (?, ?, 'admin', ?, ?, 1, '1', 0)");
				$st->bind_param('ssis', $v['usuario'], $hash, $rolId, $v['nombres']);
				if (!$st->execute()) throw new RuntimeException('Crear administrador: ' . $st->error);

				$cfg = ['db' => ['host'=>$v['host'], 'port'=>(int)$v['port'], 'name'=>$v['name'], 'user'=>$v['user'], 'pass'=>$passDb], 'pepper' => $pepper];
				$php = "<?php\n// Generado por instalar.php. No versionar.\nreturn " . var_export($cfg, true) . ";\n";
				if (file_put_contents($dirCfg . '/config.php', $php, LOCK_EX) === false) throw new RuntimeException('No se pudo escribir config/config.php');
				@chmod($dirCfg . '/config.php', 0640);
				file_put_contents($dirCfg . '/instalado.lock', date('c') . "\n");
				// Datos iniciales de la empresa (se completan luego en Configuración)
				$st = $db->prepare("INSERT INTO configuracion (parametro, valor, estado_parametro) VALUES ('empresa_nombre', ?, '1')");
				$st->bind_param('s', $v['empresa']);
				$st->execute();
				$ok = $aplicadas;
				$instalada = true;
			} catch (Throwable $e) {
				$errores[] = 'Falló la instalación: ' . $e->getMessage() . ' (borra las tablas creadas antes de reintentar).';
			}
		}
	}
}
?><!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Instalación</title>
<style>
	:root{--bg:#f5f4f2;--card:#fff;--border:#ebe7e2;--ink:#1f1d1b;--muted:#7a746e;--primary:#e05a2b;--danger-bg:#fbe4dc;--danger:#a3290f;--ok:#2f7d4f;--ok-bg:#e3f3e9}
	@media (prefers-color-scheme: dark){:root{--bg:#171615;--card:#222120;--border:#34312f;--ink:#f1eeea;--muted:#a39d96;--danger-bg:#3d1d14;--danger:#ff9b83;--ok:#7fd29d;--ok-bg:#16301f}}
	*{box-sizing:border-box}
	body{margin:0;background:var(--bg);color:var(--ink);font:14px/1.5 system-ui,-apple-system,"Segoe UI",sans-serif}
	main{max-width:560px;margin:0 auto;padding:32px 16px 56px}
	h1{font-size:24px;margin:0 0 4px} p.sub{color:var(--muted);margin:0 0 20px}
	.card{background:var(--card);border:1px solid var(--border);border-radius:16px;padding:18px 20px;margin-bottom:16px}
	h2{font-size:14px;margin:0 0 12px}
	label{display:block;font-size:12px;color:var(--muted);margin:10px 0 4px}
	input[type=text],input[type=password],input[type=number]{width:100%;padding:9px 11px;border:1px solid var(--border);border-radius:9px;background:var(--bg);color:var(--ink);font:inherit}
	input:focus{outline:2px solid var(--primary);outline-offset:1px}
	.row{display:grid;grid-template-columns:1fr 110px;gap:10px}
	.chk{display:flex;gap:8px;align-items:center;margin-top:12px;color:var(--ink);font-size:13px}
	button{width:100%;padding:12px;border:0;border-radius:10px;background:var(--primary);color:#fff;font:inherit;font-weight:600;font-size:15px;cursor:pointer}
	button:disabled{opacity:.5;cursor:not-allowed}
	ul{list-style:none;margin:0;padding:0} li{padding:3px 0}
	.si:before{content:"✓ ";color:var(--ok)} .no:before{content:"✗ ";color:var(--danger)}
	.err{background:var(--danger-bg);color:var(--danger);border-radius:10px;padding:10px 14px;margin-bottom:16px}
	.good{background:var(--ok-bg);color:var(--ok);border-radius:10px;padding:10px 14px;margin-bottom:16px}
	a{color:#d9481c}
</style>
</head>
<body>
<main>
	<h1>Instalación</h1>
	<p class="sub">Crea la base de datos, el primer administrador y la configuración del sistema.</p>

<?php if ($ok !== null): ?>
	<div class="good"><b>Listo.</b> El sistema quedó instalado (<?= count($ok) ?> script(s) SQL aplicados).</div>
	<div class="card">
		<h2>Siguiente</h2>
		<ul>
			<li>Entra con el usuario <b><?= h($v['usuario']) ?></b>: <a href="index.php">ir al inicio de sesión</a>.</li>
			<li>En <b>Configuración → Módulos y permisos</b> activas los módulos y creas roles.</li>
			<li>Por seguridad, también puedes borrar <code>instalar.php</code> del servidor.</li>
		</ul>
	</div>
<?php elseif ($instalada): ?>
	<div class="card">
		<h2>El sistema ya está instalado</h2>
		<p>Para reinstalar, borra <code>config/config.php</code> y <code>config/instalado.lock</code> sobre una base vacía.</p>
		<p><a href="index.php">Ir al inicio de sesión</a></p>
	</div>
<?php else: ?>
	<?php if ($errores): ?><div class="err"><?php foreach ($errores as $e) echo '<div>' . h($e) . '</div>'; ?></div><?php endif; ?>

	<div class="card">
		<h2>Requisitos</h2>
		<ul><?php foreach ($req as $r) echo '<li class="' . ($r[1] ? 'si' : 'no') . '">' . h($r[0]) . '</li>'; ?></ul>
	</div>

	<form method="post" autocomplete="off">
		<div class="card">
			<h2>Base de datos</h2>
			<div class="row">
				<div><label for="host">Servidor</label><input type="text" id="host" name="host" value="<?= h($v['host']) ?>" required></div>
				<div><label for="port">Puerto</label><input type="number" id="port" name="port" value="<?= h($v['port']) ?>"></div>
			</div>
			<label for="name">Nombre de la base</label><input type="text" id="name" name="name" value="<?= h($v['name']) ?>" required>
			<label for="user">Usuario de la base</label><input type="text" id="user" name="user" value="<?= h($v['user']) ?>" required>
			<label for="pass">Clave de la base</label><input type="password" id="pass" name="pass">
			<label class="chk"><input type="checkbox" name="crear" value="1" <?= $v['crear'] ? 'checked' : '' ?>> Crear la base si no existe (el usuario necesita ese permiso)</label>
		</div>
		<div class="card">
			<h2>Tu empresa</h2>
			<label for="empresa">Nombre de la empresa</label><input type="text" id="empresa" name="empresa" maxlength="80" value="<?= h($v['empresa']) ?>" required>
			<div class="note" style="font-size:12px;color:var(--muted);margin-top:6px">Luego puedes completar RUC, dirección y logo en Configuración → Empresa y medios de pago.</div>
		</div>
		<div class="card">
			<h2>Administrador</h2>
			<label for="nombres">Nombre completo</label><input type="text" id="nombres" name="nombres" value="<?= h($v['nombres']) ?>" required>
			<label for="usuario">Usuario (máx. 15)</label><input type="text" id="usuario" name="usuario" maxlength="15" value="<?= h($v['usuario']) ?>" required>
			<label for="clave">Clave (mínimo 8)</label><input type="password" id="clave" name="clave" required>
			<label for="clave2">Repetir clave</label><input type="password" id="clave2" name="clave2" required>
		</div>
		<button type="submit" <?= $reqOk ? '' : 'disabled' ?>>Instalar</button>
	</form>
<?php endif; ?>
</main>
</body>
</html>
