<?php
// ini_set('display_errors', 0);
// ini_set('display_startup_errors', 0);
// error_reporting(E_ALL);
session_start();
require_once __DIR__ . '/permisos.php';
guardia_endpoint('productos', 'editar');

include('sdba/sdba.php'); // include main file

$respuestaOk = false;
$mensajeError = 'hasta aca bien';
//$usuario = $_SESSION['id_usr'];

//if (isset($_POST) && !empty($_POST)) {
	$id = $_POST['id'];
	$nombre = $_POST['nombre'];
	$precio_v = $_POST['precio_v'];
	//$precio_c = $_POST['precio_c'];
	$unidad = $_POST['unidad'];
	$categoria = $_POST['categoria'];
	//$marca = $_POST['marca'];
	//$color = $_POST['color'];
	//$serie = $_POST['serie'];
	// $exonerada = $_POST['exonerada'];
	// $codigo = $_POST['codigo'];
	$stockn = $_POST['stock'];
	$fecha = date("Y-m-d");

	$respuestaOk = true;
	//guardamos en tabla ventas
			
			$ventas = Sdba::table('productos');
			$ventas->where('id_producto', $id);
			$data = array('nom_prod'=>$nombre,'codigo_producto'=>'','precio_venta'=>$precio_v,'precio_compra'=>$_POST['precio_c'] ?? '','unidad_prod'=>$unidad,'categoria'=>$categoria);
			$ventas->update($data);

			require_once __DIR__ . '/producto_helper.php';
			variante_unica_sincronizar($id, $precio_v, $_POST['precio_c'] ?? 0);
			producto_guardar_costos($id, $_POST['contenido_bulto'] ?? null, $_POST['precio_c'] ?? null);
				$respuestaOk = true;
				$mensajeError = 'entro';


		

//}		

		$salidaJson = array('respuesta' => $respuestaOk,
							'mensaje' => $mensajeError);

		echo json_encode($salidaJson);


?>