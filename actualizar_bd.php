<?php
// Aplica las migraciones pendientes (sql/NN_*.sql). Solo administradores.
session_start();
include('inc/control.php');
require_once __DIR__ . '/inc/migrar.php';

if (empty($_SESSION['csrf'])) { $_SESSION['csrf'] = bin2hex(random_bytes(16)); }
if (!puede('configuracion', 'editar')) { permisos_denegar(); }

$db = db_connect();
$aplicadas = [];
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	if (!hash_equals($_SESSION['csrf'], $_POST['csrf'] ?? '')) { $error = 'Token inválido, recarga la página.'; }
	else {
		try { $aplicadas = migrar_aplicar($db, __DIR__ . '/sql'); }
		catch (Throwable $e) { $error = $e->getMessage(); }
	}
}
$pendientes = migrar_pendientes($db, __DIR__ . '/sql');
function h($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
?><!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Actualizar base de datos</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" type="text/css" href="/assets/css/bootstrap.min.css">
    <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans:wght@400;500;600;700&family=IBM+Plex+Mono:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="/assets/css/escritorio.css?v=<?= filemtime(__DIR__ . '/assets/css/escritorio.css') ?>">
    <script src="https://unpkg.com/@phosphor-icons/web@2.1.1"></script>
</head>
<body class="esc">
<?php menu_v2('8'); ?>
<main class="esc-main">
    <div class="esc-title"><div><div class="fecha">Configuración</div><h1>Actualizar base de datos</h1></div></div>
    <section class="esc-card" style="max-width:640px">
        <div class="esc-head">
            <span class="esc-chip" style="background:oklch(0.94 0.05 230);color:oklch(0.55 0.15 230)"><i class="ph-duotone ph-database"></i></span>
            <h2>Migraciones</h2>
        </div>
        <?php if ($error): ?><div class="esc-band"><?= h($error) ?></div><?php endif; ?>
        <?php if ($aplicadas): ?><div class="esc-callout">Aplicadas: <?= h(implode(', ', $aplicadas)) ?></div><?php endif; ?>
        <?php if ($pendientes): ?>
            <div class="esc-note">Pendientes de aplicar:</div>
            <?php foreach ($pendientes as $p): ?><div class="esc-row-item"><span class="nm" style="text-transform:none"><?= h($p) ?></span></div><?php endforeach; ?>
            <form method="post"><input type="hidden" name="csrf" value="<?= h($_SESSION['csrf']) ?>">
                <div class="esc-actions"><button class="esc-btn primary" type="submit">Aplicar ahora</button></div></form>
        <?php else: ?>
            <div class="esc-empty">La base de datos está al día.</div>
        <?php endif; ?>
    </section>
</main>
</body>
</html>
