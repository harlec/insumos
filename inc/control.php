<?php

session_start();

if ($_SESSION['ingress']== true) {

	$usuario = $_SESSION['usuario'];
	
}
else{
	header("Location: index.php");
	exit;
}

require_once __DIR__ . '/permisos.php';
require_once __DIR__ . '/empresa.php';
requiere_pagina(); // bloquea la página si el rol no tiene permiso sobre su módulo


// Fuentes, íconos y estilos del diseño Escritorio v2 (para el <head> de cada pantalla).
function esc_head() {
	$css = dirname(__DIR__) . '/assets/css/escritorio.css';
	$leg = dirname(__DIR__) . '/assets/css/escritorio-legacy.css';
	echo '<link rel="preconnect" href="https://fonts.googleapis.com">
	<link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans:wght@400;500;600;700&family=IBM+Plex+Mono:wght@400;500;600&display=swap" rel="stylesheet">
	<link rel="stylesheet" type="text/css" href="/assets/css/escritorio.css?v=' . (is_file($css) ? filemtime($css) : 1) . '">
	<link rel="stylesheet" type="text/css" href="/assets/css/escritorio-legacy.css?v=' . (is_file($leg) ? filemtime($leg) : 1) . '">
	<script src="https://unpkg.com/@phosphor-icons/web@2.1.1"></script>';
}

// Código numérico histórico de cada opción del menú -> clave de módulo.
function menu_clave($i) {
	$mapa = ['1'=>'dashboard','2'=>'usuarios','3'=>'productos','4'=>'ventas','5'=>'reportes','6'=>'compras',
	         '7'=>'clientes','8'=>'configuracion','9'=>'caja','10'=>'preventas','11'=>'gastos'];
	return $mapa[(string)$i] ?? (string)$i;
}

// Navbar clásico (Bootstrap) de las pantallas aún no migradas. Las opciones salen de los permisos del rol.
function menu($i){
	$activa = menu_clave($i);
	$png = ['dashboard'=>'dashboard.svg','usuarios'=>'users.png','clientes'=>'clientes.png','productos'=>'products.png',
	        'ventas'=>'ventas.png','caja'=>'caja.png','compras'=>'compras.png','preventas'=>'clientes.png',
	        'gastos'=>'caja.png','reportes'=>'reports.png'];
	echo '<div id="navbar" class="navbar-collapse collapse"><ul class="nav navbar-nav menu">';
	foreach (modulos_para_menu(true) as $m) {
		$img = $png[$m['clave']] ?? 'config_tablet.svg';
		echo '<li class="'.($m['clave'] == $activa ? 'active' : '').' text-center"><a title="'.htmlspecialchars($m['nombre']).'" href="/'.$m['url'].'"><img class="isvg" src="/assets/img/'.$img.'"><br><span>'.htmlspecialchars($m['nombre']).'</span></a></li>';
	}
	$enlaces = config_enlaces();
	if ($enlaces) {
		echo '<li class="'.($activa == 'configuracion' ? 'active' : '').' dropdown text-center"><a href="#" class="dropdown-toggle" data-toggle="dropdown" title="Configuración"><img class="isvg" src="/assets/img/config_tablet.svg"><br><span>Config.</span></a><ul class="dropdown-menu">';
		foreach ($enlaces as $e) {
			echo '<li><a href="/'.$e[1].'">'.htmlspecialchars($e[2]).'</a></li>';
		}
		echo '</ul></li>';
	}
	echo '</ul><ul id="right-top"><li>Hola <strong style="text-transform: uppercase;">'.htmlspecialchars($_SESSION['usuario'], ENT_QUOTES, 'UTF-8').'</strong><a href="salir.php"><img class="isvg" src="assets/img/salir.png"><br></a></li></ul></div><!--/.nav-collapse -->';
}

// Navbar rediseñado (Escritorio v2), con íconos Phosphor y opciones según permisos del rol.
// Requiere assets/css/escritorio.css y Phosphor Icons cargados en la página.
function menu_v2($i){
	$activa = menu_clave($i);
	$enlaces = config_enlaces();
	echo '<header class="esc-header" id="esc-header">
	<a class="esc-brand" href="dashboard.php">'.marca_html().'</a>
	<nav class="esc-nav" id="esc-nav" aria-label="Menú principal">';
	foreach (modulos_para_menu(true) as $m) {
		$act = ($m['clave'] == $activa);
		echo '<a class="esc-nav-item'.($act ? ' active' : '').'" href="/'.$m['url'].'" title="'.htmlspecialchars($m['nombre']).'">
			<i class="'.($act ? 'ph-fill' : 'ph-duotone').' '.htmlspecialchars($m['icono']).'"'.($act ? '' : ' style="color:oklch(0.62 0.17 '.$m['tono'].')"').'></i><span>'.htmlspecialchars($m['nombre']).'</span></a>';
	}
	// En móvil, la configuración va dentro del panel (en escritorio es el desplegable de la derecha).
	if ($enlaces) {
		echo '<div class="esc-nav-extra"><div class="esc-nav-extra-t">Configuración</div>';
		foreach ($enlaces as $e) {
			echo '<a href="/'.$e[1].'"><i class="ph '.$e[3].'"></i> '.htmlspecialchars($e[2]).'</a>';
		}
		echo '</div>';
	}
	echo '</nav>';
	if ($enlaces) {
		echo '<div class="dropdown esc-config">
		<a href="#" class="esc-nav-item dropdown-toggle" data-toggle="dropdown" title="Configuración"><i class="ph-duotone ph-gear" style="color:oklch(0.62 0.17 210)"></i><span>Config.</span></a>
		<ul class="dropdown-menu dropdown-menu-right">';
		foreach ($enlaces as $e) {
			echo '<li><a href="/'.$e[1].'"><i class="ph '.$e[3].'"></i> '.htmlspecialchars($e[2]).'</a></li>';
		}
		echo '</ul></div>';
	}
	echo '<div class="esc-user">Hola <strong>'.htmlspecialchars(strtoupper($_SESSION['usuario']), ENT_QUOTES, 'UTF-8').'</strong>
		<a class="esc-logout" href="salir.php" title="Salir"><i class="ph-bold ph-sign-out"></i></a></div>
	<button type="button" class="esc-burger" id="esc-burger" aria-label="Abrir menú" aria-expanded="false" aria-controls="esc-nav"><i class="ph ph-list"></i></button>
</header>
<script>
(function(){
	var h = document.getElementById("esc-header"), b = document.getElementById("esc-burger");
	if (!h || !b) return;
	function set(abierto){ h.classList.toggle("open", abierto); document.body.classList.toggle("esc-menu-open", abierto); b.setAttribute("aria-expanded", abierto); b.setAttribute("aria-label", abierto ? "Cerrar menú" : "Abrir menú"); b.firstElementChild.className = "ph " + (abierto ? "ph-x" : "ph-list"); }
	b.addEventListener("click", function(){ set(!h.classList.contains("open")); });
	document.addEventListener("keydown", function(e){ if (e.key === "Escape") set(false); });
	window.addEventListener("resize", function(){ if (window.innerWidth > 1179) set(false); });
})();
</script>';
}
?>