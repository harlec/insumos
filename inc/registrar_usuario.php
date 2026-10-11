<?php require_once __DIR__ . '/config.php';
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);
session_start();

include('sdba/sdba.php'); // include main file

$respuestaOk = false;
$mensajeError = 'hasta aca bien';
//$usuario = $_SESSION['id_usr'];

//if (isset($_POST) && !empty($_POST)) {

	$nombre = $_POST['nombre'];
	$usuario = $_POST['usuario'];
	$password = $_POST['password'];
	$rol = $_POST['rol'];
	$tablet_mode = isset($_POST['tablet_mode']) ? 1 : 0;
	//$tienda = $_POST['tienda'];

		$pwd = $password;
	$pwd_peppered = hash_clave($pwd);

	//verificar si usuario existe
	$usuarios = Sdba::table('usuarios');
	$usuarios->where('usuario', $usuario);
	$usuario1 = $usuarios->get_one();
	if ($usuario1) {
		$respuestaOk = false;
		$mensajeError = 'Usuario tomado, Escoge otro usuario';
	}
	else{

		$respuestaOk = true;
		//guardamos en tabla ventas
			
			$ventas = Sdba::table('usuarios');
			$data = array('id_usuario'=>'','nombres'=> $nombre,'usuario'=>$usuario,'clave'=>$pwd_peppered,'tienda'=>'1','rol'=>$rol,'estado'=>'1','tablet_mode'=>$tablet_mode);
			$ventas->insert($data);
			$venta_id = $ventas->insert_id();
			if ($venta_id) {
				$respuestaOk = true;
				$mensajeError = 'entro';
			}
	}


		

//}		

		$salidaJson = array('respuesta' => $respuestaOk,
							'mensaje' => $mensajeError);

		echo json_encode($salidaJson);


?>