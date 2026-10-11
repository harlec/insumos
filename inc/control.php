<?php

session_start();

if ($_SESSION['ingress']== true) {

	$usuario = $_SESSION['usuario'];
	
}
else{
	header("Location: index.html");
	exit;
}

require_once __DIR__ . '/permisos.php';
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
	echo '<header class="esc-header">
	<a class="esc-brand" href="dashboard.php"><span>GRUPO</span><b>AVASA</b></a>
	<nav class="esc-nav">';
	foreach (modulos_para_menu(true) as $m) {
		$act = ($m['clave'] == $activa);
		echo '<a class="esc-nav-item'.($act ? ' active' : '').'" href="/'.$m['url'].'" title="'.htmlspecialchars($m['nombre']).'">
			<i class="'.($act ? 'ph-fill' : 'ph-duotone').' '.htmlspecialchars($m['icono']).'"'.($act ? '' : ' style="color:oklch(0.62 0.17 '.$m['tono'].')"').'></i><span>'.htmlspecialchars($m['nombre']).'</span></a>';
	}
	echo '</nav>';
	$enlaces = config_enlaces();
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
</header>';
}
?>