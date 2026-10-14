<?php
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(E_ALL);
session_start();
require_once __DIR__ . '/permisos.php';
guardia_endpoint_auto();

include('sdba/sdba.php'); // include main file

$respuestaOk = false;
$mensajeError = 'hasta aca bien';
//$usuario = $_SESSION['id_usr'];

//if (isset($_POST) && !empty($_POST)) {
	$id = $_GET['id'];
	$respuestaOk = true;
	//guardamos en tabla ventas
			
			$ventas = Sdba::table('variante_p');
			$ventas->where('id_vp', $id);
			$ventas->delete();
			
				$respuestaOk = true;
				$mensajeError = 'entro';


		

//}		

		$salidaJson = array('respuesta' => $respuestaOk,
							'mensaje' => $mensajeError);

		echo json_encode($salidaJson);


?>