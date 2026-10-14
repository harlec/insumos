<?php
session_start();
require_once __DIR__ . '/permisos.php';
guardia_endpoint_auto();
include('sdba/sdba.php');

$producto = intval($_GET['producto'] ?? 0);
$result = [];

if ($producto > 0) {
    $vp = Sdba::table('variante_p');
    $vp->where('producto_vp', $producto);
    $vp->where('state_vp', '1');
    $vp->left_join('variante_vp', 'variantes', 'id_variante');
    $lista = $vp->get();
    $pq = Sdba::table('productos');
    $pq->where('id_producto', $producto);
    $prod = $pq->get_one();
    $contenido = floatval($prod['contenido_bulto'] ?? 0);
    foreach ($lista as $v) {
        $result[] = [
            'id_vp'       => $v['id_vp'],
            'variante'    => $v['variante'],
            'cantidad_vp' => floatval($v['cantidad_vp']),
            'precioc_vp'  => floatval($v['precioc_vp']),
            'contenido_bulto' => $contenido
        ];
    }
}

header('Content-Type: application/json');
echo json_encode($result);
