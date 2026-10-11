<?php
require_once __DIR__ . '/inc/empresa.php';
?><!DOCTYPE html>
<html lang="es">
<head>
	<meta charset="UTF-8">
	<title>Ingresar – <?= empresa_h(empresa('empresa_nombre')) ?></title>
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans:wght@400;500;600;700&family=IBM+Plex+Mono:wght@400;500;600&display=swap" rel="stylesheet">
	<link rel="stylesheet" type="text/css" href="assets/css/escritorio.css">
	<script src="https://unpkg.com/@phosphor-icons/web@2.1.1"></script>
</head>
<body class="esc-login">
	<div class="lg-bar"></div>
	<main class="lg-wrap">
		<div class="lg-card">
			<div class="lg-brand"><?= marca_html() ?></div>
			<div class="lg-head">
				<h1>Bienvenido</h1>
				<p>Ingresa con tu usuario para continuar</p>
			</div>
			<form id="login" class="lg-form" novalidate>
				<div class="lg-error" id="lg-error" role="alert" hidden><i class="ph-fill ph-warning-circle" style="font-size:16px;margin-top:1px"></i><span></span></div>
				<div class="lg-field">
					<label for="usuario">Usuario</label>
					<div class="lg-input"><i class="ph ph-user"></i><input type="text" name="usuario" id="usuario" autocomplete="username" autocapitalize="none" spellcheck="false" autofocus required></div>
				</div>
				<div class="lg-field">
					<label for="pass">Clave</label>
					<div class="lg-input"><i class="ph ph-lock-simple"></i><input type="password" name="pass" id="pass" autocomplete="current-password" required>
						<button type="button" class="lg-eye" id="lg-eye" aria-label="Mostrar clave"><i class="ph ph-eye"></i></button></div>
				</div>
				<button type="submit" id="btn-login" class="lg-btn"><span>Ingresar</span></button>
			</form>
			<div class="lg-foot"><?= empresa_h(empresa('empresa_lema', 'Sistema de gestión y ventas')) ?></div>
		</div>
	</main>

	<script>
	(function () {
		var form = document.getElementById('login'), btn = document.getElementById('btn-login'),
		    err = document.getElementById('lg-error'), pass = document.getElementById('pass'),
		    eye = document.getElementById('lg-eye');

		function mostrarError(msg) { err.querySelector('span').textContent = msg; err.hidden = false; }
		function cargando(on) { btn.disabled = on; btn.firstElementChild.textContent = on ? 'Ingresando…' : 'Ingresar'; }

		eye.addEventListener('click', function () {
			var ver = pass.type === 'password';
			pass.type = ver ? 'text' : 'password';
			eye.firstElementChild.className = 'ph ' + (ver ? 'ph-eye-slash' : 'ph-eye');
			eye.setAttribute('aria-label', ver ? 'Ocultar clave' : 'Mostrar clave');
		});

		form.addEventListener('submit', function (e) {
			e.preventDefault();
			err.hidden = true;
			if (!form.usuario.value.trim() || !pass.value) { mostrarError('Escribe tu usuario y tu clave.'); return; }
			cargando(true);
			fetch('/inc/loginJs.php', {
				method: 'POST',
				headers: { 'X-Requested-With': 'XMLHttpRequest' },
				credentials: 'same-origin',
				body: new FormData(form)
			})
			.then(function (r) { return r.json(); })
			.then(function (j) {
				if (j.respuesta) { document.location.href = j.redirect || 'dashboard.php'; return; }
				mostrarError(j.mensaje || 'No puede ingresar');
				cargando(false); pass.select();
			})
			.catch(function () { mostrarError('No se pudo conectar con el sistema. Inténtalo de nuevo.'); cargando(false); });
		});
	})();
	</script>
</body>
</html>
