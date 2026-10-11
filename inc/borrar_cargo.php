<?php require_once __DIR__ . '/config.php';
// Borrado suave (estado='0'), mismo patrón que inc/borrar_producto.php.
session_start();
require_once __DIR__ . '/permisos.php';
guardia_endpoint_auto();
header('Content-Type: application/json');

if (!isset($_SESSION['id_usr'])) {
    echo json_encode(['ok' => false, 'mensaje' => 'Sin sesión']);
    exit;
}

$id = intval($_GET['id'] ?? 0);
if ($id <= 0) {
    echo json_encode(['ok' => false, 'mensaje' => 'Falta indicar el cargo']);
    exit;
}

$conn = db_connect();
$conn->set_charset('utf8');

if ($conn->connect_error) {
    echo json_encode(['ok' => false, 'mensaje' => 'DB: ' . $conn->connect_error]);
    exit;
}

$conn->query("UPDATE cargos SET estado = '0' WHERE id_cargo = $id");
$conn->close();

echo json_encode(['ok' => true]);
