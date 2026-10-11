<?php require_once __DIR__ . '/config.php';
session_start();
require_once __DIR__ . '/permisos.php';
guardia_endpoint('usuarios', 'editar');

include('sdba/sdba.php'); // include main file

$respuestaOk = false;
$mensajeError = 'hasta aca bien';
//$usuario = $_SESSION['id_usr'];

//if (isset($_POST) && !empty($_POST)) {
	$id = $_POST['id'];
	$nombre = $_POST['nombre'];
	$usuario = $_POST['usuario'];
	$password = $_POST['password'];
	$rol = $_POST['rol'];
	require_once __DIR__ . '/permisos.php';
	list($rol_id, $rol_txt) = resolver_rol($rol);
	$tienda = $_POST['tienda'];
	$tablet_mode = isset($_POST['tablet_mode']) ? 1 : 0;

		$pwd = $password;
	$pwd_peppered = hash_clave($pwd);

	
	$respuestaOk = true;
	if (empty($password)) {
		$data = array('nombres'=> $nombre,'usuario'=>$usuario,'tienda'=>$tienda,'rol'=>$rol_txt,'rol_id'=>$rol_id,'tablet_mode'=>$tablet_mode);
	}
	else{
		
		$data = array('nombres'=> $nombre,'usuario'=>$usuario,'clave'=>$pwd_peppered,'tienda'=>$tienda,'rol'=>$rol_txt,'rol_id'=>$rol_id,'tablet_mode'=>$tablet_mode);
	}
	//guardamos en tabla ventas
		
		$ventas = Sdba::table('usuarios');
		$ventas->where('id_usuario',$id);
		$ventas->update($data);

		

//}		

		$salidaJson = array('respuesta' => $respuestaOk,
							'mensaje' => $mensajeError);

		echo json_encode($salidaJson);


?>