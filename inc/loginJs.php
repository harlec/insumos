<?php require_once __DIR__ . '/config.php';
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(E_ALL);
session_start();

include('sdba/sdba.php'); // include main file

$respuestaOk = false;
$mensajeError = 'hast aca bien';

if (isset($_POST) && !empty($_POST)) {

		$usuario = $_POST['usuario'];
		$pwd = $_POST['pass'];
				$pass = hash_clave($pwd);

		$users = Sdba::table('usuarios');
		$users->where('usuario', $usuario)->and_where('clave =',$pass);
		$user_list = $users->get_one();
		//print_r($user_list);
		if ($user_list) {
			session_regenerate_id(true); // evita fijación de sesión
			$respuestaOk = true;
			$mensajeError = 'Bienvenido '.$user_list['nombres'];
			$_SESSION['usuario']  = $usuario;
			$_SESSION['ingress']  = true;
			$_SESSION['nombres']  = $user_list['nombres'];
			$_SESSION['id_usr']  = $user_list['id_usuario'];
			$_SESSION['type']    = $user_list['rol'];
			// Con roles configurables: 'admin' si el rol es administrador, 'operador' en otro caso
			// (varias pantallas aún comparan $_SESSION['type']).
			require_once __DIR__ . '/permisos.php';
			$rid = (int)($user_list['rol_id'] ?? 0);
			if ($rid && ($db = permisos_db())) {
				$rr = $db->query("SELECT nombre, es_admin FROM roles WHERE id_rol = $rid AND estado = 1");
				if ($rr && ($rol = $rr->fetch_assoc())) {
					$_SESSION['type']       = $rol['es_admin'] ? 'admin' : 'operador';
					$_SESSION['rol_nombre'] = $rol['nombre'];
				}
			}
			$_SESSION['tienda']  = $user_list['tienda'];

			// Redirigir a tablet si el usuario tiene ese modo activo
			$tablet_mode = isset($user_list['tablet_mode']) ? (int)$user_list['tablet_mode'] : 0;
			$redirect = ($tablet_mode === 1) ? 'tablet.php' : 'dashboard.php';
		}
		else{
			$respuestaOk = false;
			$mensajeError = 'No puede ingresar';
			$redirect = '';
		}

}	

		$salidaJson = array('respuesta' => $respuestaOk,
							'mensaje' => $mensajeError,
							'redirect' => $redirect);

		echo json_encode($salidaJson);


?>