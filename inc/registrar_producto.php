<?php
// ini_set('display_errors', 0);
// ini_set('display_startup_errors', 0);
// error_reporting(E_ALL);
session_start();
require_once __DIR__ . '/permisos.php';
guardia_endpoint('productos', 'crear');

include('sdba/sdba.php'); // include main file

$respuestaOk = false;
$mensajeError = 'hasta aca bien';
//$usuario = $_SESSION['id_usr'];

//if (isset($_POST) && !empty($_POST)) {

	$nombre = $_POST['nombre'];
	$precio_v = $_POST['precio_v'];
	//$precio_c = $_POST['precio_c'];
	$unidad = $_POST['unidad'];
	$categoria = $_POST['categoria'];
	//$tienda = $_POST['tienda'];
	$stockn = $_POST['stock'];
	//$exonerada = $_POST['exonerada'];
	//$fv = $_POST['fv'];
	//$marca = $_POST['marca'];
	//$codigo = $_POST['codigo'];
	//$color = $_POST['color'];
	$respuestaOk = true;
	$fecha = date("Y-m-d");

	//guardamos en tabla ventas
			
			$ventas = Sdba::table('productos');
			$data = array('id_producto'=>'','cod_sunat'=> '','serie'=>'','nom_prod'=>$nombre,'codigo_producto'=>'','unidad_prod'=>$unidad,'categoria'=>$categoria,'precio_venta'=>$precio_v,'precio_compra'=>$_POST['precio_c'] ?? '','stockp'=>$stockn,'exonerada'=>'no','proveedor'=>'1','estado'=>'1');
			$ventas->insert($data);
			$venta_id = $ventas->insert_id();
			if ($venta_id) {
				require_once __DIR__ . '/producto_helper.php';
				variante_unica_sincronizar($venta_id, $precio_v, $_POST['precio_c'] ?? 0);
				producto_guardar_costos($venta_id, $_POST['contenido_bulto'] ?? null, $_POST['precio_c'] ?? null);
				$respuestaOk = true;
				$mensajeError = 'entro';
				//guardamos en stock

				$stock = Sdba::table('stock');
				$datas = array('id_stock'=>'','producto'=>$venta_id,'ingreso'=>$stockn,'stock'=>$stockn,'motivo'=>'si','fv'=>$fecha,'stockt'=>$stockn,'fecha'=>$fecha);
				$stock->insert($datas);

				//variantes
				// $variantes = Sdba::table('variantes');
				// $datav = array('id_variante'=>'','producto'=>$venta_id,'variante'=>$fv,'stock'=>$stockn);
				// $variantes->insert($datav);
			}


		

//}		

		$salidaJson = array('respuesta' => $respuestaOk,
							'mensaje' => $mensajeError);

		echo json_encode($salidaJson);


?>