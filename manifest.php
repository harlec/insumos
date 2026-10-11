<?php
// Manifiesto PWA del POS de tablet: nombre e ícono salen de Configuración → Datos de la empresa.
require_once __DIR__ . '/inc/empresa.php';
header('Content-Type: application/manifest+json; charset=utf-8');
$nombre = empresa('empresa_nombre');
$m = [
	'name'             => $nombre . ' POS',
	'short_name'       => mb_substr($nombre, 0, 12),
	'description'      => 'Punto de venta ' . $nombre,
	'start_url'        => '/tablet.php',
	'display'          => 'fullscreen',
	'orientation'      => 'landscape',
	'background_color' => '#1a1a1a',
	'theme_color'      => '#111111',
];
if (($logo = empresa_logo_url())) {
	$m['icons'] = [['src' => strtok($logo, '?'), 'sizes' => '512x512', 'type' => mime_content_type(empresa_logo_ruta())]];
}
echo json_encode($m, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
